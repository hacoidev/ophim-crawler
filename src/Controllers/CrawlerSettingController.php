<?php

namespace Ophim\Crawler\OphimCrawler\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Support\Facades\Route;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Ophim\Crawler\OphimCrawler\Option;
use Prologue\Alerts\Facades\Alert;

class CrawlerSettingController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Setting::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/plugin/ophim-crawler/options');
        CRUD::setEntityNameStrings('crawler options', 'crawler options');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function editOptions()
    {
        $setting = Option::getEntry();

        $this->data['entry'] = $setting;

        CRUD::addField(['name' => 'fields', 'type' => 'hidden', 'value' => collect(Option::getAllOptions())->implode('name', ',')]);

        foreach (Option::getAllOptions() as $field) {
            CRUD::addField($field);
        }

        $this->crud->setOperationSetting('fields', $this->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;
        $this->data['id'] = $setting->id;

        $this->crud->addSaveAction([
            'name' => 'save_and_back',
            'redirect' => function ($crud, $request, $itemId) {
                return $crud->route;
            },
            'button_text' => 'Lưu và quay lại',
        ]);

        $this->data['saveAction'] = $this->crud->getSaveAction();

        return view('ophim-crawler::options', $this->data);
    }

    /**
     * Update the specified resource in the database.
     *
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function updateOptions(Request $request)
    {
        $this->crud->registerFieldEvents();

        // update the row in the db
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            [
                'value' => json_encode(request()->only(explode(',', request('fields'))))
            ]
        );
        $this->data['entry'] = $this->crud->entry = $item;

        Alert::success(trans('backpack::crud.update_success'))->flash();

        return back();
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param  int  $id  The id of the entry that is being edited.
     * @return array The fields with attributes, fake attributes and values.
     */
    public function getUpdateFields($id = false)
    {
        $fields = $this->crud->fields();
        $entry = Option::getEntry();
        $options = json_decode($entry->value, true) ?? [];

        foreach ($options as $k => $v) {
            $fields[$k]['value'] = $v;
        }

        if (!array_key_exists('id', $fields)) {
            $fields['id'] = [
                'name'  => $entry->getKeyName(),
                'value' => $entry->getKey(),
                'type'  => 'hidden',
            ];
        }

        return $fields;
    }
}
