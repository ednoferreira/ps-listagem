<style type="text/css" >
table { width:800px; }
th, td { border:solid 1px #ccc; padding:5px; }
</style>

<table>
        {{-- Cabeçalho com as colunas --}}
        <tr>
        @foreach($colunas as $coluna => $params)
            <th>{!! $params['coluna_link'] !!}</th>
        @endforeach
        </tr>

        {{-- Registros --}}
        @foreach ($dados as $registro)
            <tr>
            @foreach ($colunas as $coluna => $params)
                <td>{!! $registro->$coluna !!}</td>
            @endforeach
            </tr>
        @endforeach
        
</table>