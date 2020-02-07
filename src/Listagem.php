<?php

namespace Proseleta\Listagem;

class Listagem
{
    /**
     * Colunas a serem listadas na tabela.
     * @var Array
     */
    public $colunas;

    /**
     * Source: seria a fonte de dados(Model, DB::table('tabela')) que será usada nas queries
     * caso a listagem faça este gerenciamento
     */
    private $source;

    /**
     * Dados(registros) que serão listados:
     * @var Object (collection)
     */
    public $dados;

    /**
     * Campo da tabela que será mostrado na coluna ID
     * @var String
     */
    private $indice;

    /**
     * @var Boolean ativa/desativa a paginação na query. Default: true <Boolean>
     */
    public $paginacao;

    /**
     * @var Integer - quantos itens teremos por página. Default 10 <int>
     */
    public $porPagina;

    /**
     * @var Integer - a quantidade máxima permitida de itens por página
     */
    private $porPaginaMax;

    /**
     * Ações da listagem (editar, excluir, [customizados]...)
     * @var Array
     */
    private $acoes;

    /**
     * View que será usada na listagem.
     * Pode ser alterada sob demanda.
     * @var String
     */
    public $view;

    /**
     * Nome do arquivo de configuracoes
     */
    public $arquivoConfig = 'proseleta-listagem';

    public function __construct($indice = null) {

        $this->setIndice($indice);

        # verifica se há qtd de itens por página alterados pelo usuário da sessão:
        $this->checkQtdPorPagina();

        # set configs
        $this->setValoresPadrao();

        if (empty($this->view)) {
            throw new \Exception('Não foi possível ler o arquivo de configuração');
        }
    }

    /**
     * Setamos os valores padrão de configuração da listagem,
     * são itens buscados do config que o usuário pode customizar:
     */
    public function setValoresPadrao()
    {
        $this->view         = config($this->arquivoConfig.'.view');
        $this->paginacao    = config($this->arquivoConfig.'.paginacao');
        $this->porPagina    = config($this->arquivoConfig.'.porPagina');
        $this->porPaginaMax = config($this->arquivoConfig.'.porPaginaMax');
    }

    /**
     * Seta o índice da tabela:
     */
    public function setIndice($indice)
    {
        $this->indice = $indice;
    }

    /**
     * Obter o campo indice
     */
    public function getIndice()
    {
        return in_array('ID', $this->colunas) ? $this->colunas[0] : null;
    }

    /**
     * Seta a paginação
     * @param Boolean
     */
    public function setPaginacao($paginacao)
    {
        if (is_bool($paginacao)) {
            return $this->paginacao = $paginacao;
        }
        throw new \Exception('O parâmetro para setar a paginação deve ser booleano');
    }

    /**
     * Seta a quantidade de registros por página para a paginação
     * @param Integer
     */
    public function setPorPagina($quantidade)
    {
        if (is_int($quantidade)) {
            return $this->porPagina = $quantidade;
        }
        throw new \Exception('O parâmetro para setar a quantidade de itens por página deve ser do tipo inteiro.');
    }

    /**
     * Colunas a serem listadas na tabela.
     * Aceita campo => label
     * ou campo => array('parâmetros')
     * ex: 
     * $colunas => [
     *   'data_nasc' => 'Data de Nascimento',
     *   'ativo'     => ['label' => 'Ativo', 'callback' => (alguma function personalizada))]
     * ]
     */
    public function setColunas($colunas = []) 
    {
        # se existe o campo índice, será adicionado em $colunas[]:
        if (!is_null($this->indice)) {
            $colunas = [$this->indice => 'ID'] + $colunas;
        }
        
        if (!empty($colunas)) {
            foreach ($colunas as $campo => $params) {
                if (is_array($params)) {
                    # preenchemos a variável principal com os parâmetros enviados
                    $this->colunas[$campo] = $params;
                } else {
                    /**
                     * caso não tenha sido enviado nemhum parâmetro(nem label), aqui fazemos a 
                     * transformação. Basta verificar se está no formato 
                     * [0 => 'campo_tabela'] ao invés de ['campo_tabela' => 'Campo Tabela']
                     * */
                    if (is_int($campo)) {
                        $campo = $params;
                        $params = $this->label($params);
                    }
                    # considera-se que enviou só o label mesmo:
                    $this->colunas[$campo] = ['label' => $params];
                }
                # link para ordenação:                
                $this->colunas[$campo]['coluna_link'] = $this->montarLinkOrdenacao($campo);
            }
        }
    }
    
    /**
     * Source: recebe a variável que contém a Model a ser utilizada na busca dos registros.
     * No caso a query deve vir sem o get(), pois ele indica a finalização do processo de 
     * busca (não pode haver ordenação ou where's após o get(), por exemplo...), 
     * então o get() é feito neste método ao final de tudo + paginação.
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Prepara a query para a listagem dos dados:
     */
    public function prepararQuery()
    {
        if (is_null($this->source)) {
            return null;
        }
        # inicia o builder caso tenha passado a Model pura, para não dar exception ao tentar montar query numa string:
        $source = (is_string($this->source)) ? $this->source::query() : $this->source;

        # verificamos se tem ordenação:
        if (request()->get('ord') !== null) {
            $source = $source->orderBy(request()->get('ord'), (request()->get('dir') !== null) ? request()->get('dir') : 'ASC');
        }

        # Busca?
        if (request()->get('busca') !== null) {
            foreach ($this->colunas as $campo => $params) {
                $source = $source->orWhere($campo, 'LIKE', '%'.request()->get('busca').'%');
            }
        }

        # Paginação:
        if ($this->paginacao) {
            # verificamos se a paginação foi alterada pelo usuário:
            if (request()->get('pp') !== null && ((int)request()->get('pp') > 0 && (int)request()->get('pp') < $this->porPaginaMax)) {
                $this->porPagina = request()->get('pp');
                # salva na sessão para reaproveitar durante a navegação do usuário:
                $this->salvarPorPagina($this->porPagina);
            }
            $source = $source->paginate($this->porPagina);
        } else {
            $source = $source->get();
        }

        $this->setDados($source);

        return;
    }

    /**
     * Setamos os dados que serão exibidos na listagem:
     */
    public function setDados($dados)
    {
        $this->dados = $dados;
    }

    /**
     * Montagem dos dados antes de serem exibidos.
     * Percorremos todos os $this->dados e fazemos as ações necessárias, 
     * como customizar o campo, callbacks...
     */
    public function prepararDados()
    {
        # prepara os dados com callbacks e etc...
        if (!empty($this->dados)) {
            # passamos por todos os registros:
            foreach ($this->dados as $index => $registro) {
                # passamos pelas colunas de cada registro para verificar customizações:
                foreach ($this->colunas as $campo => $params) {
                    foreach ($params as $item => $valor) {
                        switch ($item) {
                            case 'callback':
                                $this->dados[$index]->$campo = $params['callback']($registro->$campo);
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Verifica se o índice realmente existe em $dados:
     */
    public function checkIndice()
    {
        if (!empty($this->indice) && is_object($this->dados) && !isset($this->dados->first()->{$this->indice})) {
            throw new \Exception('Listagem: o índice informado ('.$this->indice.') não existe na coleção de dados.');
        }
    }

    /**
     * Renderiza a view de listagem:
     * @param $view customizada(opcional)
     */
    public function render($view = '')
    {
        $this->checkIndice();
        $this->prepararQuery();
        $this->prepararDados();

        $resposta = [
            'colunas'   => $this->colunas,
            'dados'     => $this->dados,
            'paginacao' => $this->paginacao,
            'porPagina' => $this->porPagina,
        ];

        # é uma requisição ajax? Retornaremos só o json
        if (request()->ajax()) {
            return response()->json($resposta);
        }

        # template padrão do pacote, que pode ser customizado
        return view((empty($view)? $this->view : $view), $resposta);
    }

    /**
     * Montar o link de ordenação de cada coluna.
     * O objetivo é inserir os parâmetros ordem(ord) e direção(dir) ao link para que, ao ser clicado, volte
     * para a mesma página com a nova ordem/direção e mantendo parâmetros que possam existir anteriormente(como busca, etc...)
     * @param $campo String <nome original do campo da tabela>
     */
    public function montarLinkOrdenacao($campo)
    {
        # se já existe ordem, verificamos a direção para mudá-la
        $dir = 'ASC';
        if (isset($_GET['ord']) && $_GET['ord'] == $campo) {
            $dir = $_GET['dir'] == 'ASC' ? 'DESC' : 'ASC';
        }

        # removemos da query string a "ord" e "dir" antigas:
        $array = request()->query();
        unset($array['ord'], $array['dir']);
        # remonta a query string:
        $query_string = http_build_query($array);
        $separador = (empty($query_string)) ? '' : '&';
        $url = request()->url().'?'.$query_string.$separador.'ord='.$campo.'&dir='.$dir;
        return '<a href="'.$url.'" >'.$this->colunas[$campo]['label'].'</a>';
    }

    /**
     * Transformar o nome do campo em um "label" mais amigável
     * @param String $campo
     */
    public function label($campo)
    {
        $campo = str_replace('_', ' ', $campo);
        return ucfirst($campo);
    }

    /**
     * Ações da listagem (editar, excluir, etc...)
     * @param Array
     */
    // public function setAcoes($acoes)
    // {
    //     if (is_array($acoes)) {
    //         return $this->acoes = $acoes;
    //     }        
    //     throw new \Exception('Listagem: parâmetro inválido para o setAcoes()');
    // }

    /**
     * Manter na sessão a configuração "porPagina" caso seja alterada pelo usuário:
     */
    public function salvarPorPagina($qtd)
    {
        return request()->session()->put('porPagina', $qtd);
    }

    /**
     * Verifica se temos a quantidade porPagina customizada:
     */
    public function checkQtdPorPagina()
    {
        if (request()->session()->has('porPagina')) {
            $this->porPagina = request()->session()->get('porPagina');
        }
    }

}