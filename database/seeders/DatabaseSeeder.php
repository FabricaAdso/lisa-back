<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {

        $this->call([
            RolesSeeder::class,
            DocumentTypeSeeder::class,
            RegionalSeeder::class,
            TrainingCenterSeeder::class,
            UserRegisterSeeder::class,
            KnowledgeNetworkSeeder::class,
            ProgramSeeder::class,
            CourseSeeder::class,
            SubjectSeeder::class,
            RapSeeder::class,
            InstructorSeeder::class,
            SessionSeeder::class,
            ApprenticeSeeder::class,
            AssistanceSeeder::class,
            JustificationSeeder::class,
            AprobationSeeder::class,
        ]);
    }
}
