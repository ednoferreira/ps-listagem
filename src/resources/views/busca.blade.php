
<form action="{{ request()->url() }}" method="get" />
    @if(request()->get('busca') !== null) 
        {{-- para limpar a busca: --}}
        <a href="{{ request()->url() }}" title="limpar busca" >X</a> 
    @endif
    <input type="text" name="busca" value="{{ request()->get('busca') }}" placeholder="Buscar" />
    <button type="submit" >Buscar</button>
</form>