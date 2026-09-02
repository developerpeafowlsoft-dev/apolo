<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneraleSettingRequest;
use App\Models\Currency;
use App\Repositories\GeneraleSettingRepository;

class GeneraleSettingController extends Controller
{
    /**
     * Display a listing of the generale settings.
     */
    public function index()
    {
        $currencies = Currency::all();

        return view('admin.generale-setting', compact('currencies'));
    }

    /**
     * Update the generale settings.
     */
    public function update(GeneraleSettingRequest $request)
    {
        // store generale settings from generaleSettingRepository
        GeneraleSettingRepository::updateByRequest($request);

        return back()->withSuccess(__('Generale settings updated successfully'));
    }

    /**
     * Run the latest update script.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCommand()
    {
        $commands = config('installer.update_commands');

        $errors = [];

        $changeToBasePath = 'cd ' . base_path();
        foreach($commands as $command){
            try {
                shell_exec($changeToBasePath . ' && ' . $command);
            } catch (\Throwable $th) {
                $errors[] = $th->getMessage();
            }
        }

        if(!empty($errors)){
            return back()->with('runUpdateScriptError', $errors);
        }

        return back()->withSuccess(__('Latest Script Run Successfully'));
    }
}
