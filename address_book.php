<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./index.css">
</head>

<body>
    <?php
    // Подключение к базе данных
    $host = 'localhost';
    $username = 'root';
    $password = 'root';
    $database = 'mydb';
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Ошибка подключения к базе данных: " . $conn->connect_error);
    }

    // Создание таблицы, если она не существует
    $sql = "CREATE TABLE IF NOT EXISTS address_book (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        surname VARCHAR(50) NOT NULL,
        name VARCHAR(50) NOT NULL,
        patronymic VARCHAR(50),
        telephone VARCHAR(20) NOT NULL,
        address VARCHAR(255) NOT NULL
    )";
    if ($conn->query($sql) === false) {
        die("Ошибка создания таблицы: " . $conn->error);
    }

    // Обработка формы создания/редактирования записи
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create'])) {
            $surname = $_POST['surname'];
            $name = $_POST['name'];
            $patronymic = $_POST['patronymic'];
            $telephone = $_POST['telephone'];
            $address = $_POST['address'];

            $sql = "INSERT INTO address_book (surname, name, patronymic, telephone, address)
                    VALUES ('$surname', '$name', '$patronymic', '$telephone', '$address')";

            if ($conn->query($sql) === false) {
                echo "Ошибка при создании записи: " . $conn->error;
            }
        } elseif (isset($_POST['edit'])) {
            $id = $_POST['id'];
            $surname = $_POST['surname'];
            $name = $_POST['name'];
            $patronymic = $_POST['patronymic'];
            $telephone = $_POST['telephone'];
            $address = $_POST['address'];

            $sql = "UPDATE address_book SET
                    surname = '$surname',
                    name = '$name',
                    patronymic = '$patronymic',
                    telephone = '$telephone',
                    address = '$address'
                    WHERE id = $id";

            if ($conn->query($sql) === false) {
                echo "Ошибка при редактировании записи: " . $conn->error;
            }
        }
    }

    // Обработка запроса на удаление записи
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $sql = "DELETE FROM address_book WHERE id = $id";
        if ($conn->query($sql) === false) {
            echo "Ошибка при удалении записи: " . $conn->error;
        }
    }

    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $sql = "SELECT * FROM address_book WHERE id = $id";
        $result = $conn->query($sql);
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $surname = $row['surname'];
            $name = $row['name'];
            $patronymic = $row['patronymic'];
            $telephone = $row['telephone'];
            $address = $row['address'];
        }
    } else {
        // Сброс значений полей при создании новой записи
        $id = '';
        $surname = '';
        $name = '';
        $patronymic = '';
        $telephone = '';
        $address = '';
    }


    // Получение всех записей из таблицы
    $sql = "SELECT * FROM address_book";

    // Поиск с выбором поля
    if (isset($_GET['search']) && isset($_GET['field']) && !empty($_GET['search'])) {
        $search = $_GET['search'];
        $field = $_GET['field'];

        $sql = "SELECT * FROM address_book WHERE $field LIKE '%$search%'";
    }

    $result = $conn->query($sql);
    ?>

    <div class="container">
        <h2>Адресная книга</h2>

        <!-- Форма создания/редактирования записи -->
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : ''; ?>">
            <div class="form-group">
                <label>Фамилия:</label>
                <input type="text" name="surname" value="<?php echo isset($surname) ? $surname : ''; ?>" required>
            </div>
            <br>
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
            </div>
            <br>
            <div class="form-group">
                <label>Отчество:</label>
                <input type="text" name="patronymic" value="<?php echo isset($patronymic) ? $patronymic : ''; ?>" required>
            </div>
            <br>
            <div class="form-group">
                <label>Номер телефона:</label>
                <input type="text" name="telephone" value="<?php echo isset($telephone) ? $telephone : ''; ?>" required>
            </div>
            <br>
            <div class="form-group">
                <label>Адрес:</label>
                <input type="text" name="address" value="<?php echo isset($address) ? $address : ''; ?>" required>
            </div>
            <br>
            <?php if (isset($id) && !empty($id)) : ?>
                <input class="action" type="submit" name="edit" value="Редактировать">
            <?php else : ?>
                <input class="action" type="submit" name="create" value="Создать">
            <?php endif; ?>
        </form>
    </div>


    <br>

    <!-- Форма поиска -->
    <div class="search-container">
        <form class="search-form" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label>Поле поиска:</label>
            <select name="field">
                <option value="surname">Фамилия</option>
                <option value="name">Имя</option>
                <option value="patronymic">Отчество</option>
                <option value="telephone">Номер телефона</option>
                <option value="address">Адрес</option>
            </select>
            <input type="text" name="search" placeholder="Введите значение для поиска">
            <input type="submit" value="Поиск">
        </form>
    </div>

    <br>

    <!-- Кнопка "Отменить поиск" -->
    <a class="cancel-button" href="<?php echo $_SERVER['PHP_SELF']; ?>">Вывести все значения</a>

    <br><br>

    <!-- Таблица со списком записей -->
    <table>
        <tr>
            <th>Фамилия</th>
            <th>Имя</th>
            <th>Отчество</th>
            <th>Номер телефона</th>
            <th>Адрес</th>
            <th>Действия</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $row['surname']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['patronymic']; ?></td>
                <td><?php echo $row['telephone']; ?></td>
                <td><?php echo $row['address']; ?></td>
                <td>
                    <a class="delete" href="<?php echo $_SERVER['PHP_SELF'] . '?delete=' . $row['id']; ?>">Удалить</a>
                    <a class="edit" href="<?php echo $_SERVER['PHP_SELF'] . '?edit=' . $row['id']; ?>">Редактировать</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <?php
    // Закрытие соединения с базой данных
    $conn->close();
    ?>
</body>

</html>