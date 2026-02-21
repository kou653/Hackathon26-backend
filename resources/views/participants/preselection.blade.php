<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Espace SDI: Test de présélection') }}
        </h2>

    </x-slot>

    @if(Auth::user()->etudiant->getEquipe()->niveau_id <= 2) <div x-data="data()">

        <div x-show="!start" class="gap-4 px-6 py-12 md:grid md:grid-cols-6">

            <div class="col-span-2 "></div>

            <div class="col-span-2 ">

                <div class="px-4 py-6 text-xl bg-white shadow-xl sm:rounded-lg">
                    @if(Auth::user()->etudiant->getEquipe()->statut == 0)

                    @if(Auth::user()->etudiant->getEquipe()->niveau->quiz_available == 1)

                    @if(Auth::user()->etudiant->getEquipe()->qsession->quiz->state == 1)

                    @if(Auth::user()->etudiant->getEquipe()->qsession->state == 0 && Auth::user()->etudiant->getEquipe()->qsession->score == 0)

                    <p class="font-bold text-center text-md">
                        Le quiz des préselections est ouvert !
                    </p>
                    <p class="text-center mt-4">
                        Ce quiz est composé de {{sizeof(Auth::user()->etudiant->getEquipe()->qsession->quiz->questions)}} questions. <br>
                        <span class="text-red-600">>Vous disposez de 49 secondes par question</span> <br>
                        <span class="text-red-600">>Les questions apparaissent une et une seule fois</span> <br>
                        <span class="text-red-600">>Si vous rafraichissez ou quittez la page durant le test, seules les questions <br>
                            auquelles vous avez répondues sont prises en compte et votre test prend fin.
                        </span> <br>
                        <span class="text-red-600">>Le quiz débute une fois que vous cliquez sur le bouton "COMMENCER LE TEST"</span> <br>
                    </p>
                    <div class="text-center mt-5">
                        <button @click="start = true" onclick="begin()" class="px-6 py-3 mb-1 mr-1 text-sm font-bold text-white uppercase transition-all duration-150 rounded shadow outline-none ease-linearbg-emerald-500 bg-myblue hover:shadow-lg focus:outline-none">
                            Commencer le test
                        </button>
                    </div>

                    @elseif(Auth::user()->etudiant->getEquipe()->qsession->state == 1 && Auth::user()->etudiant->getEquipe()->qsession->score > 0)

                    <p class="font-bold text-center text-md">
                        Vous avez terminé le quiz. <br> Les résultats seront bientôt disponibles, veillez patienter !
                    </p>

                    @endif

                    @else


                    @if(Auth::user()->etudiant->getEquipe()->qsession->state == 0)

                    <p class="font-bold text-center text-md">
                        Les quizs sont fermés.
                    </p>

                    @elseif(Auth::user()->etudiant->getEquipe()->qsession->state == 1 && Auth::user()->etudiant->getEquipe()->qsession->score > 0)

                    <p class="font-bold text-center text-md">
                        Vous avez terminé le quiz. <br> Les résultats seront bientôt disponibles, veillez patienter !
                    </p>

                    @elseif(Auth::user()->etudiant->getEquipe()->qsession->score < 0) <img src=" {{asset('images/app/lose.svg')}} " class="loseLogo">
                        <p class="font-bold text-center text-red-600 text-md">
                            Dommage, la prochaine fois sera la bonne !
                        </p>

                        @endif

                        @endif

                        @endif

                        @else
                        <img src=" {{asset('images/app/winner.svg')}} " class="winLogo">
                        <p class="font-bold text-green-600 text-center text-md">
                            Félicitations votre équipe est séléctionneé !!
                        </p>
                        @endif
                </div>
            </div>
        </div>


        <div x-show="start == true" class="py-6">

            <div class="mx-auto max-w-8xl sm:px-6 lg:px-8">

                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">

                    <div class="w-full h-full">

                        <div class="bg-white">

                            <div class="w-full h-full ">

                                <div class="px-4 py-5 ">
                                    @livewire('participants.quiz')
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        </div>

        @else

        <div class="gap-4 px-6 py-12 md:grid md:grid-cols-6">

            <div class="col-span-2 ">

                <div class="px-4 py-6 text-xl bg-white shadow-xl sm:rounded-lg">

                    <p class="text-center">

                        Chère capitaine, les thèmes de présélection sont disponibles.
                        Il vous faudra donc faire une vidéo comprise entre 3min minimum et
                        5min maximum où vous présenterez une solution face à un sujet du thème.

                        Nous vous invitons également à nous rejoindre sur WhatsApp pour de plus
                        amples informations.

                        <u><a class="text-green-600 font-bold" href="https://chat.whatsapp.com/JK5sFrSt3V0JoBNrHJOhMl?mode=gi_t">Groupe WhatsApp</a></u>

                        <br><br>
                        NB : La vidéo doit être remise au plus tard le <span class="text-red-600 font-bold">Mardi 24 janvier 2023 à 18h00</span>
                        au numéro WhatsApp suivant : <span class="font-bold">+2250566398781</span>
                    </p>

                </div>

            </div>


            <div class="col-span-2 ">

                <div class="px-4 py-6 text-xl bg-white shadow-xl sm:rounded-lg">
 
                    <u><h1 class="font-bold">Thème:</h1></u> <br>

                    @if(Auth::user()->etudiant->getEquipe()->niveau_id == 5)
                    <p class="font-bold">
                        DÉTECTION DES VIRUS DANS UN SYSTÈME INFORMATIQUE
                    </p>

                    @elseif(Auth::user()->etudiant->getEquipe()->niveau_id == 1)
                    <p class="font-bold">
                        5G ET MEDECINE
                    </p>

                    @else
                    <p class="font-bold">
                        VR / AR
                    </p>
                    @endif

                </div>

            </div>

        </div>


        @endif



        <x-slot name="scripts">

            <script>
                function data() {
                    return {
                        start: false,
                    }
                }

                function begin() {
                    Livewire.emit('openSession')
                    document.getElementById('seconds').innerText = "49"
                    document.getElementById('counts').innerText = "49"
                    var count = setInterval(function() {

                        var sec = parseInt(document.getElementById('counts').innerText)
                        const ind = parseInt(document.getElementById('ind').innerText)
                        const ques = parseInt(document.getElementById('ques').innerText)

                        sec -= 1

                        if (sec == -1) {
                            if (ind + 1 <= ques)
                                Livewire.emit('storeAndMove', 1)
                            else
                                Livewire.emit('storeAndExit')

                            sec = 49
                        }

                        if (sec < 10)
                            sec = "0" + String(sec)

                        document.getElementById('seconds').innerText = sec
                        document.getElementById('counts').innerText = sec

                    }, 1000)
                }
            </script>

        </x-slot>


</x-app-layout>
