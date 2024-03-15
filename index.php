<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Document</title>
</head>
<body>
    <form method="POST">
        <label for="f">Función:</label><br>
        <input type="text" id="f" name="f"><br>
        <label for="a">a:</label><br>
        <input type="text" id="a" name="a"><br>
        <label for="b">b:</label><br>
        <input type="text" id="b" name="b"><br>
        <label for="max">Max:</label><br>
        <input type="text" id="max" name="max"><br>
        <input type="submit" value="Submit">
    </form>

    <canvas id="myChart"></canvas>

    <?php
    require_once 'vendor/autoload.php';

    use MathParser\StdMathParser;
    use MathParser\Interpreting\Evaluator;

    // bisection.php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $a = filter_input(INPUT_POST, 'a', FILTER_VALIDATE_FLOAT);
        $b = filter_input(INPUT_POST, 'b', FILTER_VALIDATE_FLOAT);
        $func = $_POST['f'];

        // Validar que a y b sean números
        if ($a === false || $b === false) {
            echo "Por favor, ingrese números válidos para 'a' y 'b'.";
            exit;
        }

        $parser = new StdMathParser();
        $AST = $parser->parse($func);
        $evaluator = new Evaluator();

        $f = function($x) use ($AST, $evaluator) {
            $evaluator->setVariables(['x' => $x]);
            return $AST->accept($evaluator);
        };

        function bisection($a, $b, $f) {
            $c = $a;
            while (($b - $a) >= 0.01) {
                // Encuentra el punto medio
                $c = ($a + $b) / 2;

                // Comprueba si el punto medio es la raíz
                if ($f($c) == 0.0)
                    break;

                // Decide el lado para continuar. Ahora es ascendente.
                if ($f($c)*$f($a) > 0)
                    $a = $c;
                else
                    $b = $c;
            }
            return $c;
        }

        $root = bisection($a, $b, $f);

        // Genera los datos para la gráfica
        $labels = [];
        $dataFunc = [];
        $dataRoot = [];
        $dataA = [];
        $dataB = [];
        $dataRootPoint = [];
        $max = filter_input(INPUT_POST, 'max', FILTER_VALIDATE_FLOAT); // Obtén el valor máximo del formulario
        for ($x = -10; $x <= $max; $x += 0.1) {
            $labels[] = $x;
            $dataFunc[] = $f($x);
            $dataRoot[] = round($x, 1) == round($root, 1) ? $f($x) : null;
            $dataA[] = $x == $a ? $f($x) : null; // Agrega el valor de f(x) en 'a' y 'null' en los demás puntos
            $dataB[] = $x == $b ? $f($x) : null; // Agrega el valor de f(x) en 'b' y 'null' en los demás puntos
            $dataRootPoint[] = round($x, 1) == round($root, 1) ? $f($x) : null; // Agrega el valor de f(x) en la raíz y 'null' en los demás puntos
        }

        echo "La raíz es: " . $root;
    } else {
        echo "Por favor, use el método POST para enviar los datos.";
    }
    ?>

    <script>
        // Obtén los datos de la gráfica del código PHP
        var labels = <?php echo json_encode($labels); ?>;
        var dataFunc = <?php echo json_encode($dataFunc); ?>;
        var dataRoot = <?php echo json_encode($dataRoot); ?>;
        var dataA = <?php echo json_encode($dataA); ?>;
        var dataB = <?php echo json_encode($dataB); ?>;
        var dataRootPoint = <?php echo json_encode($dataRootPoint); ?>;

        // Obtén el contexto del canvas
        var ctx = document.getElementById('myChart').getContext('2d'); 
        // Crea la gráfica
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'f(x)',
                    data: dataFunc,
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }, {
                    label: 'Root',
                    data: dataRoot,
                    fill: false,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }, {
                    label: 'a',
                    data: dataA,
                    fill: false,
                    borderColor: 'rgb(255, 205, 86)',
                    tension: 0.1
                }, {
                    label: 'b',
                    data: dataB,
                    fill: false,
                    borderColor: 'rgb(255, 205, 86)',
                    tension: 0.1
                }, {
                    label: 'Root Point',
                    data: dataRootPoint,
                    fill: false,
                    borderColor: 'rgb(0, 0, 0)',
                    pointRadius: 5,
                    pointHoverRadius: 10
                }]
            },
            options: {
                scales: {
                    x: {
                        type: 'linear',
                        grace: '5%'
                    },
                    y: {
                        type: 'linear',
                        grace: '5%'
                    }
                },
                plugins: {
                    zoom: {
                        pan: {
                            enabled: true,
                            mode: 'xy'
                        },
                        zoom: {
                            enabled: true,
                            mode: 'xy'
                        }
                    }
                }
            }
        });
</script>
</body>
</html>