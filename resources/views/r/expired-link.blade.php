<x-layout>
	<div class="min-h-screen bg-background flex items-center justify-center p-4">
		<div class="max-w-md w-full text-center">
			<div class="w-16 h-16 rounded-full bg-destructive/10 flex items-center justify-center mx-auto mb-6">
				<span class="text-3xl font-light text-destructive">!</span>
			</div>

			<h1 class="text-2xl font-medium mb-2">Link expirado</h1>
			<p class="text-muted-foreground mb-6">
				O link que você está tentando acessar não está mais disponível.
			</p>

			<a href="{{ url('/') }}" class="text-sm text-foreground underline underline-offset-4 hover:text-foreground/80">
				Voltar para a página inicial
			</a>

			<div class="mt-12 pt-6 border-t border-border">
				<p class="text-xs text-muted-foreground">
					Este é um projeto de estudo.
					<a href="{{ url('/') }}" class="underline underline-offset-2 hover:text-foreground">
						Saiba mais
					</a>
				</p>
			</div>
		</div>
	</div>
</x-layout>
