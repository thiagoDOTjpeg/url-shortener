
<x-layouts.auth>
	<x-slot:title>
		Esqueceu a senha? - Shortly
	</x-slot:title>

	<div class="min-h-screen bg-background flex flex-col">
		<main class="flex-1 flex items-center justify-center px-6 py-12">
			<div class="w-full max-w-sm">
				<div class="mb-8">
					<h1 class="text-2xl font-medium mb-2">Esqueceu a senha?</h1>
					<p class="text-sm text-muted-foreground">
						Informe seu e-mail para receber um link de redefinição de senha.
					</p>
				</div>

				@if (session('status'))
					<div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm flex items-center shadow-sm">
						<x-lucide-check-circle class="w-5 h-5 mr-3 shrink" />
						<p>{{ session('status') }}</p>
					</div>
				@endif

				<form action="{{ route('password.email') }}" method="POST" class="space-y-4">
					@csrf

					<div class="space-y-2">
						<x-label for="email">Email</x-label>
						<x-input
							id="email"
							name="email"
							type="email"
							placeholder="seu@email.com"
							:value="old('email')"
							required
							autofocus
						/>
						@error('email')
						<p class="text-xs text-destructive mt-1">{{ $message }}</p>
						@enderror
					</div>

					<x-button type="submit" class="w-full">
						Enviar link de redefinição
					</x-button>
				</form>

				<p class="mt-6 text-center text-sm text-muted-foreground">
					Lembrou da senha?
					<a href="{{ route('login.form') }}" class="text-foreground hover:underline font-medium">
						Entrar
					</a>
				</p>
			</div>
		</main>
	</div>
</x-layouts.auth>
