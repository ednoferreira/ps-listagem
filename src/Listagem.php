<?php

namespace Proseleta\Listagem;

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
    public function setDados($array = [])
    {
        $this->dados = $array;
    }

    /**
     * Montagem dos dados antes de serem exibidos.
     * Percorremos todos os $this->dados e fazemos as ações necessárias:
     */
    public function prepararDados()
    {
        if (!empty($this->dados)) {
            # passamos por todos os registros:
            foreach ($this->dados as $index => $registro) {
                # passamos pelas colunas de cada registro para verificar customizações:
                foreach ($this->colunas as $campo => $params) {
                    foreach ($params as $item => $valor) {
                        switch ($item) {
                            case 'flag':
                                $this->dados[$index][$campo] = '<'.$params['callback']($registro[$campo]);
                            break;
                            case 'callback':
                                $this->dados[$index][$campo] = $params['callback']($registro[$campo]);
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