<?php

namespace Proseleta\Listagem;
use Illuminate\Support\Facades\URL;

class Listagem
{
    /**
     * Colunas a serem listadas na tabela.
     */
    public $colunas = [];
    /**
     * Dados(registros) que serão listados:
     */
    public $dados = [];

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
                # se já existe ordem, verificamos a direção para mudá-la
                $dir = 'ASC';
                if (isset($_GET['ord']) && $_GET['ord'] == $campo) {
                    $dir = $_GET['dir'] == 'ASC' ? 'DESC' : 'ASC';
                }
                $this->colunas[$campo]['coluna_link'] = '<a href="'.URL::current().'?ord='.$campo.'&dir='.$dir.'" >'.$this->colunas[$campo]['label'].'</a>';
            }
        }

        //dd($this->colunas);
    }

    /**
     * Dá um reset na variável colunas
     */
    public function limparColunas()
    {
        $this->colunas = [];
    }

    /**
     * Setamos os dados que serão exibidos:
     * Recebe um array
     */
    public function setDados($dados)
    {
        $this->dados = $dados;
    }

    /**
     * Montagem dos dados antes de serem exibidos.
     * Percorremos todos os $this->dados e fazemos as ações necessárias:
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

    public function render()
    {
        $this->prepararDados();
        # template padrão do pacote, que pode ser customizado: namespace/nome_da_view
        return view('listagem::listagem', [
            'colunas' => $this->colunas,
            'dados'   => $this->dados,
        ]);
    }

}