<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Rules\CronExpression as CronExpressionRule;

class BackupScheduleController
{
    public function __construct(
        protected readonly BackupSchedulesUiResponder $ui
    ) {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return $this->ui->renderCreateBackupSchedule(
            FilesystemConfiguration::query()
                ->active()
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'type' => [
                'required',
                'string',
                Rule::enum(BackupTypes::class),
            ],
            'cron_expression' => ['required', 'string', new CronExpressionRule],
            'is_active' => 'sometimes|boolean',
            'destination_ids' => 'sometimes|array|min:1',
            'destination_ids.*' => [
                'integer',
                Rule::exists(FilesystemConfiguration::class, 'id')->where(
                    'is_active',
                    true,
                ),
            ],
        ];

        $validated = $request->validate($rules);

        $schedule = BackupSchedule::create(collect($validated)->except('destination_ids')->all());

        if (array_key_exists('destination_ids', $validated)) {
            $schedule->filesystemConfigurations()->sync($validated['destination_ids']);
        }

        return $this->ui->renderStoreBackupSchedule($schedule);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BackupSchedule $schedule)
    {
        $selectedDestinationIds = $schedule
            ->filesystemConfigurations()
            ->pluck('filesystem_configurations.id')
            ->all();

        $destinations = FilesystemConfiguration::query()
            ->where(function ($query) use ($selectedDestinationIds) {
                $query->active();

                if (count($selectedDestinationIds) > 0) {
                    $query->orWhereIn('id', $selectedDestinationIds);
                }
            })
            ->orderBy('name')
            ->get();

        return $this->ui->renderEditBackupSchedule($schedule, $destinations);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BackupSchedule $schedule)
    {
        $rules = [
            'name' => 'sometimes|string|max:255',
            'type' => [
                'sometimes',
                'string',
                Rule::enum(BackupTypes::class),
            ],
            'cron_expression' => ['sometimes', 'string', new CronExpressionRule],
            'is_active' => 'sometimes|boolean',
            'destination_ids' => 'sometimes|array|min:1',
            'destination_ids.*' => [
                'integer',
                Rule::exists(FilesystemConfiguration::class, 'id')->where(
                    'is_active',
                    true,
                ),
            ],
        ];

        $validated = $request->validate($rules);

        $schedule->update(collect($validated)->except('destination_ids')->all());

        if (array_key_exists('destination_ids', $validated)) {
            $schedule->filesystemConfigurations()->sync($validated['destination_ids']);
        }

        return $this->ui->renderUpdateBackupSchedule($schedule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BackupSchedule $schedule)
    {
        $schedule->delete();

        return $this->ui->renderDestroyBackupSchedule($schedule);
    }
}
