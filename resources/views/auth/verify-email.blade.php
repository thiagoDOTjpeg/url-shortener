<x-layouts.auth>
    <main class="flex flex-col h-dvh justify-center m-auto max-w-md">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 text-gray-600 rounded-full mb-6">
                    <x-lucide-mail class="w-8 h-8" />
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('Verifique seu e-mail') }}
                </h2>

                <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                    {{ __('Obrigado por se cadastrar! Antes de começar, você poderia verificar seu endereço de e-mail clicando no link que acabamos de enviar? Se você não recebeu o e-mail, enviaremos outro com prazer.') }}
                </p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm flex items-center shadow-sm">
                    <x-lucide-check-circle class="w-5 h-5 mr-3 shrink" />
                    <p>{{ __('Um novo link de verificação foi enviado para o endereço de e-mail fornecido durante o registro.') }}</p>
                </div>
            @endif

            <div class="flex flex-col space-y-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-button class="w-full justify-center py-3">
                        <x-lucide-rotate-cw class="w-4 h-4 mr-2" />
                        {{ __('Reenviar e-mail de verificação') }}
                    </x-button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="text-center">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-900 underline underline-offset-4 transition-colors">
                        {{ __('Sair da conta') }}
                    </button>
                </form>
            </div>
        </div>
    </main>
</x-layouts.auth>
