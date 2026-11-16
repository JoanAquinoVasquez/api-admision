<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait ScopesToAdmissionProcess
{
    protected static function bootScopesToAdmissionProcess(): void
    {
        static::addGlobalScope('admission_process', function (Builder $builder): void {
            $processId = static::currentAdmissionProcessId();
            if ($processId === null || DB::getDefaultConnection() !== 'mysql_unificada') {
                return;
            }

            $model = $builder->getModel();
            $builder->where($model->qualifyColumn('proceso_admision_id'), $processId);
        });

        static::creating(function (Model $model): void {
            $processId = static::currentAdmissionProcessId();
            if ($processId !== null
                && DB::getDefaultConnection() === 'mysql_unificada'
                && ! $model->getAttribute('proceso_admision_id')) {
                $model->setAttribute('proceso_admision_id', $processId);
            }
        });
    }

    protected static function currentAdmissionProcessId(): ?int
    {
        if (app()->runningInConsole() || ! app()->bound('request')) {
            return null;
        }

        $process = app('request')->attributes->get('proceso_admision');
        return $process?->id === null ? null : (int) $process->id;
    }
}
