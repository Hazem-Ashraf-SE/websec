<?php
namespace App\Http\Controllers\Web;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use Artisan;

use App\Http\Controllers\Controller;
use App\Models\User;

class UsersController extends Controller {

	use ValidatesRequests;

    public function list(Request $request) {
        if (!auth()->check()) {
            return redirect('/login');
        }
        
        if(!auth()->user()->hasPermissionTo('show_users')) {
            abort(401);
        }
        
        $query = User::select('*');
        
        // If user is Employee, only show Customer role users
        if (auth()->user()->hasRole('Employee')) {
            $query->whereHas('roles', function($q) {
                $q->where('name', 'Customer');
            });
        }
        
        $query->when($request->keywords, 
        fn($q)=> $q->where("name", "like", "%$request->keywords%"));
        $users = $query->get();
        
        // Load roles for each user
        foreach ($users as $user) {
            $user->load('roles');
        }
        
        return view('users.list', compact('users'));
    }

	public function register(Request $request) {
        return view('users.register');
    }

    private function ensureCustomerRoleExists()
    {
        // Check if Customer role exists
        $customerRole = Role::where('name', 'Customer')->first();
        
        // If not, create it
        if (!$customerRole) {
            $customerRole = Role::create([
                'name' => 'Customer',
                'guard_name' => 'web'
            ]);
            
            // Clear cache to ensure role is recognized
            Artisan::call('cache:clear');
        }
        
        return $customerRole;
    }

    public function doRegister(Request $request) {
        try {
            $this->validate($request, [
                'name' => ['required', 'string', 'min:5'],
                'email' => ['required', 'email', 'unique:users'],
                'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->mixedCase()->symbols()],
            ]);
        }
        catch(\Exception $e) {
            return redirect()->back()->withInput($request->input())->withErrors('Invalid registration information.');
        }

        DB::beginTransaction();
        try {
            // Create the user
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();

            // Get or create Customer role
            $customerRole = Role::firstOrCreate(
                ['name' => 'Customer'],
                ['guard_name' => 'web']
            );

            // Get or create Buy Item permission
            $buyItemPermission = Permission::firstOrCreate(
                ['name' => 'buy item'],
                [
                    'display_name' => 'Buy Item',
                    'guard_name' => 'web'
                ]
            );

            // Assign role and permission to user
            $user->assignRole($customerRole);
            $user->givePermissionTo($buyItemPermission);

            // Clear cache
            Artisan::call('cache:clear');
            
            DB::commit();
            return redirect('/');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration error: ' . $e->getMessage());
            return redirect()->back()->withInput($request->input())->withErrors('Error during registration. Please try again.');
        }
    }

    public function login(Request $request) {
        return view('users.login');
    }

    public function doLogin(Request $request) {
    	
    	if(!Auth::attempt(['email' => $request->email, 'password' => $request->password]))
            return redirect()->back()->withInput($request->input())->withErrors('Invalid login information.');

        $user = User::where('email', $request->email)->first();
        Auth::setUser($user);

        return redirect('/');
    }

    public function doLogout(Request $request) {
    	
    	Auth::logout();

        return redirect('/');
    }

    public function profile(Request $request, User $user = null) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = $user??auth()->user();
        if(auth()->id()!=$user->id) {
            if(!auth()->user()->hasPermissionTo('show_users')) abort(401);
        }

        // Get unique permissions by using a collection with unique constraint
        $permissions = collect($user->getAllPermissions())
            ->unique(function ($permission) {
                return strtolower($permission->name);
            })
            ->values();

        // Load purchased products
        $purchasedProducts = DB::table('purchases')
            ->join('products', 'purchases.product_id', '=', 'products.id')
            ->where('purchases.user_id', $user->id)
            ->select('products.*', 'purchases.created_at as purchase_date')
            ->get();

        return view('users.profile', compact('user', 'permissions', 'purchasedProducts'));
    }

    public function edit(Request $request, User $user = null) {
   
        $user = $user??auth()->user();
        if(auth()->id()!=$user?->id) {
            if(!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }
    
        $roles = [];
        foreach(Role::all() as $role) {
            $role->taken = ($user->hasRole($role->name));
            $roles[] = $role;
        }

        // Get unique permissions using collection
        $permissions = collect(Permission::all())
            ->unique(function ($permission) {
                return strtolower($permission->name);
            })
            ->values()
            ->map(function ($permission) use ($user) {
                $permission->taken = $user->hasDirectPermission($permission->name);
                return $permission;
            })
            ->all();

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    public function save(Request $request, User $user) {

        if(auth()->id()!=$user->id) {
            if(!auth()->user()->hasPermissionTo('show_users')) abort(401);
        }

        $user->name = $request->name;
        $user->save();

        if(auth()->user()->hasPermissionTo('admin_users')) {

            $user->syncRoles($request->roles);
            $user->syncPermissions($request->permissions);

            Artisan::call('cache:clear');
        }

        //$user->syncRoles([1]);
        //Artisan::call('cache:clear');

        return redirect(route('profile', ['user'=>$user->id]));
    }

    public function delete(Request $request, User $user) {
        // Check if user is trying to delete their own account or has delete_users permission
        if (auth()->id() != $user->id && !auth()->user()->hasPermissionTo('delete_users')) {
            abort(401);
        }

        // Get the authenticated user before deletion
        $authUser = auth()->user();

        try {
            DB::beginTransaction();

            // Delete related credit transactions
            DB::table('credit_transactions')->where('customer_id', $user->id)->delete();
            DB::table('credit_transactions')->where('employee_id', $user->id)->delete();

            // Delete related purchases
            DB::table('purchases')->where('user_id', $user->id)->delete();

            // Delete role assignments
            DB::table('model_has_roles')->where('model_id', $user->id)->delete();
            DB::table('model_has_permissions')->where('model_id', $user->id)->delete();

            // If user is deleting their own account, logout first
            if (auth()->id() == $user->id) {
                Auth::logout();
            }

            // Delete the user
            $user->delete();

            DB::commit();

            // If user deleted their own account, redirect to home
            if ($authUser->id == $user->id) {
                return redirect('/')->with('success', 'Your account has been deleted successfully.');
            }

            // If admin deleted another user, redirect to users list
            return redirect()->route('users')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete user. Please try again.');
        }
    }

    public function editPassword(Request $request, User $user = null) {

        $user = $user??auth()->user();
        if(auth()->id()!=$user?->id) {
            if(!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }

        return view('users.edit_password', compact('user'));
    }

    public function savePassword(Request $request, User $user) {

        if(auth()->id()==$user?->id) {
            
            $this->validate($request, [
                'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->mixedCase()->symbols()],
            ]);

            if(!Auth::attempt(['email' => $user->email, 'password' => $request->old_password])) {
                
                Auth::logout();
                return redirect('/');
            }
        }
        else if(!auth()->user()->hasPermissionTo('edit_users')) {

            abort(401);
        }

        $user->password = bcrypt($request->password); //Secure
        $user->save();

        return redirect(route('profile', ['user'=>$user->id]));
    }
}