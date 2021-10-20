<?php
require __DIR__ . '/vendor/autoload.php';

# Возвращает соединение с БД
function get_mysql_connection(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn =  new mysqli("mysql", "user", "password", "accounts");

    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    return $conn;
}

# Получает список с информацией о счетах
function get_accounts_info(mysqli $conn): array {
    $result = $conn->query("SELECT id, account_name, amount FROM accounts");

    if (!$result) {
        echo "Ошибка во время выполнения запроса: (" . $conn->errno . ") " . $conn->error;
        exit();
    }

    $accounts = [];
    foreach ($result as $row) {
        $accounts[] = [
            "id" => $row['id'],
            "name" => $row['account_name'],
            'amount' => $row['amount']
        ];
    }

    return $accounts;
}

# Переносит сумму $amount со счета с id $from на счет с id $to
function transfer_money(mysqli $conn, string $from, string $to, int $amount) {
    $conn->autocommit(false);

    $stmtFrom = $conn->prepare("UPDATE accounts SET amount = amount - ? WHERE id = ?");
    $stmtFrom->bind_param("ii", $amount, $from);
    if (!$stmtFrom->execute()) {
        printf("Invalid transaction: %s\n", mysqli_connect_error());
        $conn->rollback();
        exit();
    }

    $stmtTo = $conn->prepare("UPDATE accounts SET amount = amount + ? WHERE id = ?");
    $stmtTo->bind_param("ii", $amount, $to);
    if (!$stmtTo->execute()) {
        printf("Invalid transaction: %s\n", mysqli_connect_error());
        $conn->rollback();
        exit();
    }

    $conn->commit();
}

# Генерирует страницу с ответом
function render_accounts_list(mysqli $conn) {
    $loader = new \Twig\Loader\FilesystemLoader('.');
    $twig = new \Twig\Environment($loader);

    $accounts = get_accounts_info($conn);

    echo $twig->render('index.html',
        ['accounts' => $accounts]
    );
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $conn = get_mysql_connection();
        render_accounts_list($conn);
        $conn->close();
        break;
    case 'POST':
        $conn = get_mysql_connection();
        transfer_money($conn, $_POST['from'], $_POST['to'], (int)$_POST['amount']);
        render_accounts_list($conn);
        $conn->close();
        break;
    default:
        echo 'Invalid method';
        break;
}
