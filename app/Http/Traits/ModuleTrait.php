<?php

namespace App\Http\Traits;

use App\Module;

trait ModuleTrait
{
    /**
     * Get modules
     * @return \Illuminate\Http\Response
     */
    public function fetchModules()
    {
        try {
            $modules = Module::select('id', 'module')
                ->active()
                ->get();

            return $modules;
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
