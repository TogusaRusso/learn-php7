<?php 
use Bookstore\Domain\Book as Book; 
use Bookstore\Domain\Customer;
use Bookstore\Domain\Customer\CustomerFactory;

use Bookstore\Exceptions\InvalidIdException;
use Bookstore\Exceptions\ExceededMaxAllowedException;

use Bookstore\Utils\Config;

/*
*require_once __DIR__ . '/Book.php'; 
*require_once __DIR__ . '/Customer.php'; 
*/


//function __autoload($classname) { 
function autoloader($classname) {
    $lastSlash = strpos($classname, '\\') + 1; 
    $classname = substr($classname, $lastSlash);
    $directory = str_replace('\\', '/', $classname); 
    $filename = __DIR__ . '/src/' . $directory . '.php';
    require_once($filename); 
}
spl_autoload_register('autoloader');



function addSale(int $userId, array $bookIds): void { 
    $dbConfig = Config::getInstance()->get('db');
    $db = new PDO(
        'mysql:host=' . getenv('IP') . ';dbname=bookstore;port=3306',
        $dbConfig['user'], 
        $dbConfig['password'] 
    );
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->beginTransaction(); 
    try { 
        $query = 'INSERT INTO sale (customer_id, date) ' 
            . 'VALUES(:id, NOW())'; 
        $statement = $db->prepare($query); 
        if (!$statement->execute(['id' => $userId])) { 
            throw new Exception($statement->errorInfo()[2]); 
        } 
        $saleId = $db->lastInsertId(); 
        $query = 'INSERT INTO sale_book (book_id, sale_id) ' 
            . 'VALUES(:book, :sale)'; 
        $statement = $db->prepare($query); 
        $statement->bindValue('sale', $saleId); 
        foreach ($bookIds as $bookId) { 
            $statement->bindValue('book', $bookId); 
            if (!$statement->execute()) { 
                throw new Exception($statement->errorInfo()[2]); 
            } 
        } 
        $db->commit(); 
    } catch (Exception $e) { 
        $db->rollBack(); 
        throw $e; 
    } 
}

try { 
    addSale(1, [1, 2, 3]); 
} catch (Exception $e) { 
    echo 'Error adding sale: ' . $e->getMessage(); 
}

/*
$query = 'SELECT * FROM book WHERE author = :author';
$statement = $db->prepare($query);
$statement->bindValue('author', 'George Orwell');
$statement->execute(); 
$rows = $statement->fetchAll();
//var_dump($rows);

$query = <<<SQL
INSERT INTO book (isbn, title, author, price) 
VALUES (:isbn, :title, :author, :price) 
SQL;
$statement = $db->prepare($query); 
$params = [
    'isbn' => '9781412108614', 
    'title' => 'Iliad', 
    'author' => 'Homer', 
    'price' => 9.25
]; 
$statement->execute($params); 
echo $db->lastInsertId(); // 8

$query = <<<SQL
INSERT INTO book (isbn, title, author, price) 
VALUES ("9788187981954", "Peter Pan", "J. M. Barrie", 2.34) 
SQL;
$result = $db->exec($query); 
var_dump($result); // false 
$error = $db->errorInfo()[2]; 
var_dump($error); // Duplicate entry '9788187981954' for key 'isbn'
echo $db->lastInsertId('id'); // 8

$percentage = 0.16;
$addTaxes = function (array &$book, $index) use ($percentage) { 
    if (isset($book['price'])) { 
        $book['price'] += round($percentage * $book['price'], 2); 
    } 
};

$books = [ 
    ['title' => '1984', 'price' => 8.15], 
    ['title' => 'Don Quijote', 'price' => 12.00], 
    ['title' => 'Odyssey', 'price' => 3.55] 
]; 

$percentage = 100000;
array_walk($books, $addTaxes);
var_dump($books);


function checkIfValid(Customer $customer, array $books): bool { 
    return $customer->getAmountToBorrow() >= count($books);
}

$book1 = new Book(9785267006323, "1984", "George Orwell", 12); 
$book2 = new Book(9780061120084, "To Kill a Mockingbird", "Harper Lee", 2); 

function createBasicCustomer($id) { 
    try { 
        echo "<br>\nTrying to create a new customer.<br>\n"; 
        return CustomerFactory::factory(
            "basic",
            "name",
            "surname",
            "email",
            $id
        ); 
    } catch (InvalidIdException $e) { 
        echo "You cannot provide a negative id.<br>\n"; 
    } catch (ExceededMaxAllowedException $e) {
        echo "No more customers are allowed.<br>\n"; 
    } catch (Exception $e) { 
        echo "Unknown exception: " . $e->getMessage() . "<br>\n"; 
    } finally { 
        echo "End of function.<br>\n"; 
    } 
}
createBasicCustomer(1); 
createBasicCustomer(-1);
createBasicCustomer(55);

$basic1 = new Basic("name", "surname", "email", 1); 
$premium = new Premium("name", "surname", "email"); 
var_dump($basic1->getId()); // 1 
var_dump($premium->getId()); // 2
*/