<?php
/*
 * Consulta de municípios do IBGE
 * API:
 * https://servicodados.ibge.gov.br/api/v1/localidades/municipios/
 */

// Lista de estados brasileiros
$estados = [
    '11' => 'Rondônia',
    '12' => 'Acre',
    '13' => 'Amazonas',
    '14' => 'Roraima',
    '15' => 'Pará',
    '16' => 'Amapá',
    '17' => 'Tocantins',
    '21' => 'Maranhão',
    '22' => 'Piauí',
    '23' => 'Ceará',
    '24' => 'Rio Grande do Norte',
    '25' => 'Paraíba',
    '26' => 'Pernambuco',
    '27' => 'Alagoas',
    '28' => 'Sergipe',
    '29' => 'Bahia',
    '31' => 'Minas Gerais',
    '32' => 'Espírito Santo',
    '33' => 'Rio de Janeiro',
    '35' => 'São Paulo',
    '41' => 'Paraná',
    '42' => 'Santa Catarina',
    '43' => 'Rio Grande do Sul',
    '50' => 'Mato Grosso do Sul',
    '51' => 'Mato Grosso',
    '52' => 'Goiás',
    '53' => 'Distrito Federal'
];

$municipios = [];
$erro = null;
$estadoSelecionado = $_GET['estado'] ?? '';

/*
 * Consulta a API quando um estado for selecionado
 */
if ($estadoSelecionado !== '') {

    // Valida o código da UF
    if (isset($estados[$estadoSelecionado])) {

        $url = "https://servicodados.ibge.gov.br/api/v1/localidades/estados/"
             . urlencode($estadoSelecionado)
             . "/municipios";

        // Faz a requisição usando cURL
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,

            // Desabilita a validação SSL
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ]
        ]);


        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErro = curl_error($ch);

        curl_close($ch);

        if ($curlErro) {
            $erro = "Erro ao conectar com a API do IBGE: " . $curlErro;
        } elseif ($httpCode !== 200) {
            $erro = "A API do IBGE retornou o código HTTP: " . $httpCode;
        } else {

            $dados = json_decode($resposta, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $erro = "A API retornou uma resposta JSON inválida.";
            } else {
                $municipios = $dados;
            }
        }

    } else {
        $erro = "Estado selecionado inválido.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Municípios do Brasil - IBGE</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }

        .container {
            width: min(1100px, 92%);
            margin: 40px auto;
        }

        .cabecalho {
            background: #ffffff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
            margin-bottom: 25px;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #111827;
        }

        .descricao {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .formulario {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .campo {
            flex: 1;
            min-width: 250px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        select,
        input {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            background: white;
        }

        button {
            padding: 13px 25px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .resultado {
            margin-bottom: 20px;
        }

        .resultado h2 {
            margin-bottom: 5px;
        }

        .contador {
            color: #6b7280;
        }

        .busca {
            margin-bottom: 20px;
        }

        .municipios {
            display: grid;
            grid-template-columns:
                repeat(auto-fill, minmax(240px, 1fr));
            gap: 15px;
        }

        .municipio {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border-left: 5px solid #2563eb;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
            transition: .2s;
        }

        .municipio:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(0,0,0,.10);
        }

        .municipio h3 {
            margin: 0 0 10px;
            color: #111827;
        }

        .codigo {
            font-size: 13px;
            color: #6b7280;
        }

        .erro {
            padding: 15px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
            margin-top: 20px;
        }

        .vazio {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            color: #6b7280;
        }

        @media (max-width: 600px) {

            .container {
                width: 95%;
                margin: 20px auto;
            }

            .cabecalho {
                padding: 20px;
            }

            button {
                width: 100%;
            }
        }

    </style>
</head>

<body>

<div class="container">

    <div class="cabecalho">

        <h1>Municípios do Brasil</h1>

        <div class="descricao">
            Selecione um estado para consultar os municípios
            diretamente na API do IBGE.
        </div>

        <form method="GET">

            <div class="formulario">

                <div class="campo">

                    <label for="estado">
                        Estado
                    </label>

                    <select
                        name="estado"
                        id="estado"
                        required
                    >

                        <option value="">
                            Selecione um estado
                        </option>

                        <?php foreach ($estados as $codigo => $nome): ?>

                            <option
                                value="<?= htmlspecialchars($codigo) ?>"
                                <?= $estadoSelecionado === $codigo
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= htmlspecialchars($nome) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button type="submit">
                    Consultar municípios
                </button>

            </div>

        </form>

    </div>


    <?php if ($erro): ?>

        <div class="erro">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>


    <?php if ($estadoSelecionado !== '' && !$erro): ?>

        <div class="resultado">

            <h2>
                Municípios de
                <?= htmlspecialchars(
                    $estados[$estadoSelecionado]
                ) ?>
            </h2>

            <div class="contador">
                <?= count($municipios) ?>
                municípios encontrados
            </div>

        </div>


        <?php if (count($municipios) > 0): ?>

            <div class="busca">

                <input
                    type="search"
                    id="filtro"
                    placeholder="Digite o nome do município..."
                    onkeyup="filtrarMunicipios()"
                >

            </div>


            <div class="municipios" id="listaMunicipios">

                <?php foreach ($municipios as $municipio): ?>

                    <div
                        class="municipio"
                        data-nome="<?= htmlspecialchars(
                            strtolower($municipio['nome'])
                        ) ?>"
                    >

                        <h3>
                            <?= htmlspecialchars(
                                $municipio['nome']
                            ) ?>
                        </h3>

                        <div class="codigo">
                            Código IBGE:
                            <?= htmlspecialchars(
                                $municipio['id']
                            ) ?>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="vazio">
                Nenhum município encontrado.
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>


<script>

function filtrarMunicipios() {

    const filtro =
        document
            .getElementById('filtro')
            .value
            .toLowerCase();

    const municipios =
        document.querySelectorAll('.municipio');

    municipios.forEach(function(municipio) {

        const nome =
            municipio.dataset.nome;

        if (nome.includes(filtro)) {
            municipio.style.display = '';
        } else {
            municipio.style.display = 'none';
        }

    });
}

</script>

</body>
</html>
