<table>
        {{-- Cabeçalho com as colunas --}}
        <th>
        @foreach($colunas as $coluna => $params)
            <td>{!! $params['coluna_link'] !!}</td>
        @endforeach
        </th>

        {{-- Registros --}}
        @foreach ($dados as $registro)
            <tr>
            @foreach ($colunas as $coluna => $params)
                <td>{{ $registro->$coluna }}</td>
            @endforeach
            </tr>
        @endforeach
        
</table>