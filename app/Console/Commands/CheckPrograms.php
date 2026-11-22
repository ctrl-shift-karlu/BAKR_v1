<?php

namespace App\Console\Commands;

use App\Matching\ProgramCatalog;
use App\Models\Program;
use Illuminate\Console\Command;

class CheckPrograms extends Command
{
    protected $signature = 'bakr:check-programs';

    protected $description = 'Show how every DNSC program in the database is mapped to a scholarship field';

    public function handle(): int
    {
        $rows = Program::with('campus')->orderBy('campus_id')->orderBy('program_name')->get()->map(fn ($p) => [
            $p->campus?->campus_name ?? '-', $p->program_name, ProgramCatalog::fieldFor($p->program_name),
        ]);

        if ($rows->isEmpty()) {
            $this->warn('The programs table is empty. Add the DNSC programs first.');

            return self::SUCCESS;
        }

        $this->table(['Campus', 'Program', 'Mapped field'], $rows->all());
        $unknown = $rows->where(2, ProgramCatalog::UNRECOGNIZED)->count();
        $unknown
            ? $this->warn("{$unknown} program(s) are 'unrecognized'. Add a line for each in config/program_fields.php.")
            : $this->info('Every program is mapped.');

        return self::SUCCESS;
    }
}
