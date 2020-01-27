
# ps-listagem
pacote de listagens para o proseleta

Exemplo de uso:

	<?php

	namespace App\Http\Controllers\Admin;

	use App\Http\Controllers\Controller;
	use Proseleta\Listagem\Listagem;
	use Carbon\Carbon;

	class ClientesController extends Controller
	{

		public function index()
		{
			$listagem = new Listagem();
			$listagem->setColunas([
				'nome' => ['label' => 'Nome', 'callback' => function($nome) {
					return strtoupper($nome);
				}],
				'data_nasc' => [
					'label' => 'Data de nascimento', 
					'callback' => function($data) {
						return Carbon::parse($data)->format('d/m/Y');
					}
				]
			]);
			$listagem->setDados([
				0 => ['nome' => 'Edno Nunes Ferreira', 'data_nasc' => '1985-04-17'],
				1 => ['nome' => 'Fernanda Oliveira', 'data_nasc' => '1985-09-13'],
				2 => ['nome' => 'Beltrano Soares', 'data_nasc' => '1985-03-10'],
			])
			;
			return $listagem->render();
		}

	}
  
  # Resultado (beta):
  
  Nome 	 --------                Data de nascimento <br>
EDNO NUNES FERREIRA ------   	     17/04/1985 <br>
FERNANDA OLIVEIRA	------       13/09/1985 <br>
BELTRANO SOARES ------	         10/03/1985 <br>
