<?php

class TakeInput
{

    /**
     * stores all the data in csv file
     *
     * @var array
     */
    protected $computersAndLatency = array();

    /*
     * stores the input data from users
     * @var string
     *
     */
    protected $inputLine;

    /*
     * stores the csv filename provided by user
     * @var string
     *
     */
    protected $filename;

    /**
     * function to read the file with the provided by the user
     *
     * @return boolean true|false
     *         true if the file read successfully
     *         false if the file was not readable or the file does not exist
     */
    public function readFile()
    {
        /* replaces the end of line characher from the filename */
        $fileName = str_replace(PHP_EOL, '', $this->filename);
        if ($fileName === "quit") {
            echo "see you again!!\n";
            exit();
        }
        /* checks if file exists */
        if (file_exists($fileName)) {
            /* opens the file in read-only mode */
            if (($handle = fopen($fileName, "r")) !== FALSE) {
                While (($line = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $numberOfColumns = count($line);
                    /* returns false if the csv file has more than 3 columns in a line */
                    if ($numberOfColumns !== 3) {
                        echo ("improper data format\n");
                        return FALSE;
                        /* checks if the latency in the csv file is a numeric value */
                    } elseif (! is_numeric($line[2])) {
                        echo "Latency not an Integer\n";
                        return FALSE;
                    }
                    /* replaces all the whitespace charachter from the individual data in the csv file */
                    for ($i = 0; $i < $numberOfColumns; $i ++) {
                        $line[$i] = str_replace(' ', '', $line[$i]);
                    }
                    $this->computersAndLatency[] = $line;
                }
                fclose($handle);
                return TRUE;
                /* prints error message if reading the file was not possible */
            } else {
                echo "\nERROR reading file: " . $fileName . "\n";
                return FALSE;
            }
            /* prints error if the file doesnot exist */
        } else {
            echo "File not found: " . $fileName . "\n";
            return FALSE;
        }
    }

    /**
     * reads the filename from the user in commandline and stores it in the filename variable
     */
    public function readFileName()
    {
        echo "Enter the csv file Name or type quit to exit ";
        $handle = fopen("php://stdin", "r");
        $this->filename = fgets($handle);
    }

    /**
     * reads the line of input from the user and stores it in input line variable
     */
    public function waitForInput()
    {
        echo "\nInput: ";
        $handle = fopen("php://stdin", "r");
        $this->inputLine = fgets($handle);
    }
}

?>