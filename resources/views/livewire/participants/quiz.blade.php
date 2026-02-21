<div>
    <div class="col-span-6 w-full">
        <div class="md:grid md:grid-cols-6">
            <div class="col-span-6">
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">

                                <table class="min-w-full divide-y table-auto  divide-gray-200">

                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="text-center px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-500 uppercase">
                                                {{$quiz->questions[$current_index]->content}}
                                            </th>
                                        </tr>

                                    </thead>

                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($quiz->questions[$current_index]->responses as $response)

                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="font-bold text-gray-900 text-md">
                                                            <input wire:model="sponses.{{$response->id}}" type="checkbox" name="r{{$response->id}}" id="r{{$response->id}}" />
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="font-bold text-gray-900 text-md">
                                                            <label for="r{{$response->id}}">
                                                                {{$response->content}}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="mt-2">
        <p>Questions: <span id="ind">{{$current_index + 1}}</span> / <span id="ques">{{sizeof($quiz->questions)}}</span> </p>
        <div class="mt-2 font-bold text-red-600">
            Temps restant:<span id="seconds"></span>
        </div>
    </div>

    <div class="flex justify-center gap-4 p-2 border-t">
        @if($next)
        <button class="px-4 py-2 text-sm font-bold uppercase border rounded-md cursor-pointer bg-orange text-white hover:shadow" onclick="resetTimer()" wire:click="storeAndMove(1)">Suivant</button>
        @else
        <button class="px-4 py-2 text-sm font-bold uppercase border rounded-md cursor-pointer bg-red-600 text-white hover:shadow" wire:click="storeAndExit()">Terminer</button>
        @endif
    </div>

</div>

<span id="counts" style="display: none;">18</span>

<script>
    function resetTimer() {
        document.getElementById("counts").innerText = 18
    }
</script>
