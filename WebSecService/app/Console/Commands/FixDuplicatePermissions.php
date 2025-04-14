<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class FixDuplicatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:fix-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix duplicate permissions in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for duplicate Buy Item permissions...');
        
        // Get all buy item permissions
        $buyItemPermissions = Permission::where('name', 'like', '%buy item%')->get();
        
        if ($buyItemPermissions->count() > 1) {
            $this->info('Found ' . $buyItemPermissions->count() . ' Buy Item permissions. Fixing...');
            
            // Keep the first one and update it
            $keepPermission = $buyItemPermissions->first();
            $keepPermission->name = 'buy item';
            $keepPermission->display_name = 'Buy Item';
            $keepPermission->guard_name = 'web';
            $keepPermission->save();
            
            $keepId = $keepPermission->id;
            
            // Update role_has_permissions
            DB::table('role_has_permissions')
                ->whereIn('permission_id', $buyItemPermissions->pluck('id'))
                ->where('permission_id', '!=', $keepId)
                ->update(['permission_id' => $keepId]);
                
            // Update model_has_permissions
            DB::table('model_has_permissions')
                ->whereIn('permission_id', $buyItemPermissions->pluck('id'))
                ->where('permission_id', '!=', $keepId)
                ->update(['permission_id' => $keepId]);
            
            // Delete duplicates
            Permission::where('name', 'like', '%buy item%')
                ->where('id', '!=', $keepId)
                ->delete();
                
            $this->info('Successfully fixed duplicate permissions.');
        } else {
            $this->info('No duplicate Buy Item permissions found.');
        }
        
        // Clear the cache
        $this->call('cache:clear');
    }
}
