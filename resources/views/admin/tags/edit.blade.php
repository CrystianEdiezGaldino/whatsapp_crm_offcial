@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Editar Tag</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.tags.update', $tag) }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Nome</label>
            <input type="text" id="name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('name', $tag->name) }}" required>
        </div>

        <div class="mb-4">
            <label for="color" class="block text-gray-700 font-bold mb-2">Cor</label>
            <div class="flex items-center">
                <input type="color" id="color" name="color" class="w-16 h-10 border border-gray-300 rounded-lg cursor-pointer" value="{{ old('color', $tag->color) }}" required>
                <span class="ml-4 text-sm text-gray-600" id="colorCode">{{ old('color', $tag->color) }}</span>
            </div>
        </div>

        <div class="mb-4">
            <label for="category" class="block text-gray-700 font-bold mb-2">Categoria</label>
            <select id="category" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Selecione uma categoria</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" {{ old('category', $tag->category) === $cat ? 'selected' : '' }}>
                        {{ ucfirst($cat) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="is_active" class="flex items-center">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded" {{ old('is_active', $tag->is_active) ? 'checked' : '' }}>
                <span class="ml-2 text-gray-700">Ativa</span>
            </label>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Atualizar
            </button>
            <a href="{{ route('admin.tags.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
    document.getElementById('color').addEventListener('change', function() {
        document.getElementById('colorCode').textContent = this.value;
    });
</script>
@endsection
