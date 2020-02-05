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
     * Dados(registros) que serão listados:
     * @var Object (collection)
     */
    public $dados;

    /**
     * Campo da tabela que será mostrado na coluna ID
     */
    private $indice;

    /**
     * View que será usada na listagem.
     * Pode ser alterada sob demanda.
     * @var String
     */
    public $view = 'listagem::listagem';

    public function __construct($indice = null) {
        $this->setIndice($indice);
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
     * Colunas a serem listadas na tabela.
     * Aceita campo => label
     * ou campo => array('parâmetros')
     * ex: 
     * $colunas => [
     *   'data_nasc' => 'Data de Nascimento',
     *   'ativo'     => ['label' => 'Ativo', 'flag' => true]
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
                    # considera-se que enviou só o label mesmo:
                    $this->colunas[$campo] = ['label' => $params];
                }
                # link para ordenação:                
                $this->colunas[$campo]['coluna_link'] = $this->montarLinkOrdenacao($campo);
            }
        }
    }
    
    /**
     * Source: recebe a variável que contém a Model a ser utilizada na busca
     */
    public function setSource($source)
    {
        # verificamos se tem ordenação:
        if (request()->get('ord') !== null) {
            $source = $source::orderBy(request()->get('ord'), (request()->get('dir') !== null) ? request()->get('dir') : 'ASC');
        }
        $source = $source->get();
        $this->setDados($source);
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
        $this->prepararDados();
        # template padrão do pacote, que pode ser customizado
        return view((empty($view)? $this->view : $view), [
            'colunas' => $this->colunas,
            'dados'   => $this->dados,
        ]);
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

}