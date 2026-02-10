<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Hackaton;
use App\Models\User;
use App\Models\Niveau;
use App\Models\Quiz;
use App\Models\Qvideo;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Creation des differents Niveaux
        $nvx_q = ['Niveau 1', 'Niveau 2 Développement', 'Niveau 2 Télécom'];
        $nvx_nq = ['Niveau 3 Télécom', 'Niveau 3 Développement', 'Niveau 3 Sécurité'];
        $rls = ['Super@Administrateur', 'Administrateur', 'Participant'];
        $pms = ['restaurant', 'comite nuit', 'hackaton'];

        $masters = [
            'MASTER 1 TELECOM',
            'MASTER 1 SIGL',
            'MASTER 1 CSIA',
            'MASTER 1 MBDS',
            'MASTER 1 BIHAR',
            'MASTER 2 TELECOM',
            'MASTER 2 SIGL',
            'MASTER 2 SITW',
            'MASTER 2 MDSI',
            'MASTER 2 MBDS',
            'MASTER 2 BIHAR',
            'MASTER 2 ERIS'
        ];

        // Même structure pour tous les niveaux : libellé du niveau => liste des classes (copier/coller et modifier)
        $classesParNiveau = [
            'Niveau 1' => ['ENTD', 'SRIT 1A', 'SRIT 1B', 'SRIT 1C', 'TWIN 1', 'MP2I A', 'MP2I B'],
            'Niveau 2 Développement' => ['SRIT 2A', 'SRIT 2B', 'SIGL 2', 'RTEL 2', 'TWIN 2', 'SRIT 3A', 'SRIT 3B', 'SIGL 3', 'RTEL 3', 'TWIN 3', 'DASI', 'MPI', 'CSIA'],
            'Niveau 2 Télécom' => ['SRIT 2A', 'SRIT 2B', 'SIGL 2', 'RTEL 2', 'TWIN 2', 'SRIT 3A', 'SRIT 3B', 'SIGL 3', 'RTEL 3', 'TWIN 3', 'DASI', 'MPI', 'CSIA'],
            'Niveau 3 Télécom' => $masters,
            'Niveau 3 Développement' => $masters,
            'Niveau 3 Sécurité' => $masters,
        ];

        $classes_externes = [
            'INPHB',
            'ISTC',
            'IIT',
            'Miage',
            'Université Virtuelle',
            'Autre'
        ];

        foreach ($nvx_q as $nv) {
            Niveau::firstOrCreate(
                ['libelle' => $nv],
                ['quiz_available' => 1]
            );
        }

        foreach ($nvx_nq as $nv) {
            Niveau::firstOrCreate(
                ['libelle' => $nv],
                ['quiz_available' => 1]
            );
        }

        foreach ($rls as $r) {
            Role::firstOrCreate(
                ['name' => $r, 'guard_name' => 'web']
            );
        }

        foreach ($pms as $p) {
            Role::firstOrCreate(
                ['name' => $p, 'guard_name' => 'web']
            );
        }

        $user = User::firstOrCreate(
            ['email' => 'adminHackathon@C2E.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make("@dminH@ck@thon23P@ssword!#NotreSDI")
            ]
        );

        User::firstOrCreate(
            ['email' => 'userHackathon@C2E.com'],
            [
                'name' => 'participant',
                'password' => Hash::make("@dminH@ck@thon23P@ssword-XXX-!")
            ]
        );

        Hackaton::firstOrCreate(
            ['annee' => '2022'],
            [
                'pco_1' => 'N\'DA Regis Richmond',
                'pco_2' => 'DJE BI Mointi Jean Patrice',
            ]
        );
        Hackaton::firstOrCreate(
            ['annee' => '2023'],
            [
                'pco_1' => 'BLE Yatana',
                'pco_2' => 'President YAO Daniel',
            ]
        );
        Hackaton::firstOrCreate(
            ['annee' => '2024'],
            [
                'pco_1' => 'President OUATTARA Wilfried',
                'pco_2' => 'DJE Jean-Louis',
            ]
        );
        Hackaton::firstOrCreate(
            ['annee' => '2025'],
            [
                'pco_1' => 'DJE Jean-Louis',
                'pco_2' => 'ZAMBLE Cerise',
                'inscription' => 1
            ]
        );
        Hackaton::firstOrCreate(
            ['annee' => '2026'],
            [
                'pco_1' => 'SILOUE Emmanek',
                'pco_2' => 'AGO Marc Ezéchiel',
                'inscription' => 1
            ]
        );

        foreach (Niveau::where('quiz_available', 1)->get() as $niv) {
            Quiz::firstOrCreate(
                ['niveau_id' => $niv->id],
                ['title' => 'Quiz ' . $niv->libelle]
            );
        }

        foreach (Niveau::where('quiz_available', 0)->get() as $niv) {
            Qvideo::firstOrCreate(['niveau_id' => $niv->id]);
        }

        foreach ($classesParNiveau as $niveauLibelle => $listeClasses) {
            $niv = Niveau::where('libelle', $niveauLibelle)->first();
            if (!$niv) {
                continue;
            }
            foreach ($listeClasses as $classLibelle) {
                Classe::firstOrCreate(
                    [
                        'libelle' => $classLibelle,
                        'niveau_id' => $niv->id
                    ],
                    ['esatic' => true]
                );
            }
        }

        foreach (Niveau::where('id', '>', 1)->get() as $niv) {
            foreach ($classes_externes as $cla) {
                Classe::firstOrCreate(
                    [
                        'libelle' => $cla,
                        'niveau_id' => $niv->id
                    ],
                    ['esatic' => 0]
                );
            }
        }

        if (!$user->hasRole('Super@Administrateur')) {
            $user->assignRole('Super@Administrateur');
        }
    }
}
