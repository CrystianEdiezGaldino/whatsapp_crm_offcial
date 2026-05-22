@if(session('success'))
<x-alert type="success" title="Sucesso!">{{ session('success') }}</x-alert>
@endif

@if(session('error'))
<x-alert type="error" title="Erro">{{ session('error') }}</x-alert>
@endif

@if(session('warning'))
<x-alert type="warning" title="Atenção">{{ session('warning') }}</x-alert>
@endif

@if(session('info'))
<x-alert type="info">{{ session('info') }}</x-alert>
@endif
