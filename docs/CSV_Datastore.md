# CSV Datatstore #

## Создание нового CSV файла ##

    // some code
    use rollun\files\CsvFileObject;
    // some code
    CsvFileObject::createNewCsvFile($fullFilename, ['id', 'val']);

## Кофигурации CSV файла ##

Доступные конфигурации:

    rollun\files\CsvFileObject\CsvFileObject::__construct(string $filename, $delimiter = ',', $enclosure = '"', $escape = '\\')

## Пример создания/чтения CSV файла с различными конфигурациями

Для MS Excel подойдёт следующий пример:
    
    // some code
    use rollun\files\CsvFileObject;
    // some code
    CsvFileObject::createNewCsvFile($fullFilename, ['id', 'val'], ';');
 
Google Sheets, LibreOffice к примеру используют те же требования что и [RFC 4180](https://tools.ietf.org/html/rfc4180):
     
     // some code
     use rollun\files\CsvFileObject;
     // some code
     CsvFileObject::createNewCsvFile($fullFilename, ['id', 'val']);
    
Пример чтения CSV файла с различными конфигурациями

Для MS Excel подойдёт следующий пример:
    
    // some code
    use rollun\files\CsvFileObject;
    // some code
    $cfsvFileObject = new CsvFileObject($fullFilename, ['id', 'val'], ';');
    
Google Sheets, LibreOffice:
    
    // some code
    use rollun\files\CsvFileObject;
    // some code
    $cfsvFileObject = new CsvFileObject($fullFilename, ['id', 'val']);
        

## Требования к CSV файлу ##

Первая колонка файла должна быть уникальным первичным ключом.  Желательно с именем 'id'.

После создания инстанса CSV Datatstore вы можете менять содержимое CSV файла, если сможете получить `flock(LOCK_EX)`.

Переводы строк внутри ячеек должны быть только через один спец символ: `\n`(или `\x0A`, LF - line feed)

Пустые строки будут проигнорированы, лучше воздержаться от пустых строк


