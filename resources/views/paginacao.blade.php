@if($paginacao)

    {{ $dados->appends(request()->input())->links() }}
    {{-- dica: o método appends informa ao links() que deve manter a query string ao paginar --}}

    <form action="{{ request()->url() }}" method="get" style="text-align:left;" />      

        Por página:
        <input type="number" name="pp" value="{{ request()->query('pp') ?? $porPagina }}" />

        ir para:
        <select name="page" onchange="this.form.submit()" >
            @for ($x = 1; $x <= $dados->lastPage(); $x++)
                <option value="{{ $x }}" {{ ($x == $dados->currentPage()) ? 'selected="selected"' : '' }}  >{{ $x }}</option>
            @endfor
        </select>

        {{-- para manter as query strings ao submeter o form: --}}
        @foreach(request()->query() as $item => $valor)
            @if($item != 'page' && $item != 'pp')
                <input type="hidden" name="{{ $item }}" value="{{ $valor }}" />
            @endif
        @endforeach

        <input type="submit" style="visibility: hidden;" />

    </form>

@endif