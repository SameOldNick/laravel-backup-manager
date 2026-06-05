<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Http\Request;
use SameOldNick\BackupManager\Contracts\Responders\CleanupSchedulesUiResponder;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Rules\CronExpression as CronExpressionRule;

class CleanupScheduleController
{
    public function __construct(
        protected readonly CleanupSchedulesUiResponder $ui
    ) {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return $this->ui->renderCreateCleanupSchedule();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cron_expression' => ['required', 'string', new CronExpressionRule],
            'is_active' => 'sometimes|boolean',
        ]);

        $schedule = CleanupSchedule::create($validated);

        return $this->ui->renderStoreCleanupSchedule($schedule);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CleanupSchedule $schedule)
    {
        return $this->ui->renderEditCleanupSchedule($schedule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CleanupSchedule $schedule)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'cron_expression' => ['sometimes', 'string', new CronExpressionRule],
            'is_active' => 'sometimes|boolean',
        ]);

        $schedule->update($validated);

        return $this->ui->renderUpdateCleanupSchedule($schedule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CleanupSchedule $schedule)
    {
        $schedule->delete();

        return $this->ui->renderDestroyCleanupSchedule($schedule);
    }
}
