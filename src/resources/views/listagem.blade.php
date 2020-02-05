<style type="text/css" >
.corpo * { float:none; margin-top:15px; }
table { width:800px; margin:10px auto; }
th, td { border:solid 1px #ccc; padding:5px; }
form { text-align:right; background:#dedede; }
</style>

<div class="corpo" >
    <div class="header">
        @include('listagem::busca')
    </div>

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
</div>