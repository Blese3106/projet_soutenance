<div class="space-y-6">
    <!-- Résumé du test -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-2xl font-bold text-primary-600">
                    {{ $application->test_score }}/{{ $application->test_total_points }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Score total</div>
            </div>
            <div>
                <div class="text-2xl font-bold 
                    @if($application->getTestPercentage() >= 80) text-green-600
                    @elseif($application->getTestPercentage() >= 60) text-yellow-600
                    @else text-red-600
                    @endif">
                    {{ round($application->getTestPercentage(), 1) }}%
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Pourcentage</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-primary-600">
                    @if($application->test_started_at && $application->test_completed_at)
                        {{ $application->test_started_at->diffInMinutes($application->test_completed_at) }}
                    @else
                        -
                    @endif
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Minutes</div>
            </div>
        </div>
    </div>

    <!-- Questions et réponses -->
    @foreach($questions as $index => $question)
        <div class="border dark:border-gray-700 rounded-lg p-4">
            <!-- En-tête de la question -->
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm font-medium text-gray-500">Question {{ $index + 1 }}</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            @if($question->type === 'mcq') bg-blue-100 text-blue-800
                            @elseif($question->type === 'multiple_choice') bg-green-100 text-green-800
                            @elseif($question->type === 'text') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ match($question->type) {
                                'mcq' => 'QCM',
                                'multiple_choice' => 'Choix multiples',
                                'text' => 'Texte',
                                'code' => 'Code',
                            } }}
                        </span>
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                            {{ $question->points }} {{ $question->points > 1 ? 'points' : 'point' }}
                        </span>
                    </div>
                    <p class="text-base font-medium">{{ $question->question }}</p>
                </div>
                
                @php
                    $userAnswer = $application->test_answers[$question->id] ?? null;
                    $isCorrect = $question->checkAnswer($userAnswer);
                @endphp
                
                <div class="ml-4">
                    @if($isCorrect)
                        <div class="flex items-center gap-1 text-green-600">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @else
                        <div class="flex items-center gap-1 text-red-600">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Options (pour QCM et choix multiples) -->
            @if(in_array($question->type, ['mcq', 'multiple_choice']))
                <div class="space-y-2 mb-3">
                    @foreach($question->options as $optionIndex => $option)
                        @php
                            $isUserAnswer = is_array($userAnswer) 
                                ? in_array($option, $userAnswer) 
                                : $userAnswer == $option;
                            $isCorrectAnswer = in_array($option, $question->correct_answers);
                        @endphp
                        
                        <div class="flex items-center gap-2 p-2 rounded
                            @if($isUserAnswer && $isCorrectAnswer) bg-green-50 border border-green-300
                            @elseif($isUserAnswer && !$isCorrectAnswer) bg-red-50 border border-red-300
                            @elseif($isCorrectAnswer) bg-green-50 border border-green-200
                            @else bg-gray-50 border border-gray-200
                            @endif">
                            
                            @if($question->type === 'mcq')
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center
                                    @if($isUserAnswer) border-primary-600 @else border-gray-300 @endif">
                                    @if($isUserAnswer)
                                        <div class="w-3 h-3 rounded-full bg-primary-600"></div>
                                    @endif
                                </div>
                            @else
                                <div class="w-5 h-5 rounded border-2 flex items-center justify-center
                                    @if($isUserAnswer) border-primary-600 bg-primary-600 @else border-gray-300 @endif">
                                    @if($isUserAnswer)
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                            @endif
                            
                            <span class="flex-1">{{ $option }}</span>
                            
                            @if($isCorrectAnswer)
                                <span class="text-xs text-green-600 font-medium">✓ Correct</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Réponse textuelle -->
            @if($question->type === 'text')
                <div class="space-y-2">
                    <div class="bg-gray-100 dark:bg-gray-900 p-3 rounded">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Réponse du candidat:</div>
                        <div class="font-medium">{{ $userAnswer ?? 'Aucune réponse' }}</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded">
                        <div class="text-sm text-green-600 dark:text-green-400 mb-1">Réponse attendue:</div>
                        <div class="font-medium text-green-700 dark:text-green-300">{{ $question->expected_answer }}</div>
                    </div>
                </div>
            @endif

            <!-- Code -->
            @if($question->type === 'code')
                <div class="space-y-2">
                    <div class="bg-gray-100 dark:bg-gray-900 p-3 rounded">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Code soumis:</div>
                        <pre class="text-sm font-mono whitespace-pre-wrap">{{ $userAnswer ?? 'Aucun code soumis' }}</pre>
                    </div>
                    @if($question->expected_answer)
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                            <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">Critères d'évaluation:</div>
                            <div class="text-sm">{{ $question->expected_answer }}</div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Explication -->
            @if($question->explanation)
                <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded">
                    <div class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-1">💡 Explication:</div>
                    <div class="text-sm text-blue-700 dark:text-blue-400">{{ $question->explanation }}</div>
                </div>
            @endif
        </div>
    @endforeach
</div>