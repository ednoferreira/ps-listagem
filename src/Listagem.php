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
     * View que será usada na listagem.
     * Pode ser alterada sob demanda.
     * @var String
     */
    public $view = 'listagem::listagem';

    public function __construct() {

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
        # limpa as colunas atuais se existir:
        $this->limparColunas();

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
     * 
     */
    public function limparColunas()
    {
        $this->colunas = [];
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
     * Renderiza a view de listagem:
     * @param $view customizada(opcional)
     */
    public function render($view = '')
    {
        $this->prepararDados();

        # template padrão do pacote, que pode ser customizado
        return view((empty($view)? $this->view : $view), [
            'colunas' => $this->colunas,
            'dados'   => $this->dados,
        ]);
    }

    /**
     * Montar o link de ordenação de cada coluna:
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