<?php
require_once ('TakeInput.php');

class NetworkPath extends TakeInput
{

    /**
     * stores the DeviceFrom value provided by the user
     *
     * @var string
     */
    private $firstComputer;

    /**
     * stores the DeviceTo value provided by the user
     *
     * @var string
     */
    private $finalComputer;

    /**
     * stores the RequiredLatency value provided by the user
     *
     * @var integer
     */
    private $requiredLatency;

    /**
     * stores the devices in the csv file uniquely
     *
     * @var array
     */
    private $computers = array();

    /**
     * stores all the neighbours of all the individual devices
     *
     * @var array
     */
    private $connectingComputers = array();

    /**
     * stores the total latency from the first computer to all the other computers
     * it gets set as the program looks for different neighbours for a computer in a path
     *
     * @var array
     */
    private $latency = array();

    /**
     * it is an array of all the computers that have been visited
     * a device gets added to this array after it has been visited
     *
     * @var array
     */
    private $previousComputer = array();

    /**
     * It is the chosen computer to be visited.
     * Chosen from the list of unvisited list
     *
     * @var string;
     */
    private $chosenComputer;

    /**
     * Checks if the program needs to run the shortest path algorithm or not
     * It is set as false in the begining, then once the latency from the first possible path is not met,
     * this variable is set to true
     *
     * @var boolean
     */
    private $shortestPath;

    /**
     * this variable determines if the input from user is valid or not.
     * It is set as true at first then
     * if the input is not valid, it is set to false and another input is asked
     *
     * @var boolean
     */
    private $isInputProcessed;

    /**
     * it is the sum of the latency of the current computer and the neighbour computer
     * it helps to determine which neighbour to go to
     *
     * @var integer
     */
    private $combinedLatency;

    /**
     * this function runs two menus for taking the filename from the user and
     * taking the input data from the user
     * Everything in the program happens within the menus
     */
    public function starTest()
    {
        echo "\n\n         **NETWORK PATH TEST**       \n\n";
        $this->runFileInputMenu();
        echo "\nEnter data in format:   DeviceFrom DeviceTo RequiredLatency  \n";
        $this->runDeviceInputMenu();
    }

    /**
     * this function calls the readFilename function from the TakeInput class
     * It runs a infinite while loop which gets terminated after the file is read with the
     * readFileName function in the takeInput class
     */
    private function runFileInputMenu()
    {
        while (TRUE) {
            $this->readFileName();
            if ($this->readFile() === TRUE)
                break;
        }
    }

    /**
     * this function takes the input of user stored in the $inputLine variable and processes.
     * the function runs a infinite while loop where input is taken and validated
     * It breaks if quit is typed by the user
     * Otherwise, it calls isSearchPathSuccessful function which determines if a path with required latency was found
     * if the search path is finished, output printing function is called
     */
    private function runDeviceInputMenu()
    {
        while (TRUE) {
            $this->isInputProcessed = TRUE;
            $this->waitForInput();
            $inputLine = str_replace(PHP_EOL, '', $this->inputLine);
            if ($inputLine === "quit") {
                echo "see you again!!\n";
                break;
            } else {
                $this->processInput($inputLine);
                if ($this->isInputProcessed !== TRUE) {
                    continue;
                }
                if ($this->isSearchPathSuccessful()) {
                    $this->printOutput();
                }
            }
        }
    }

    /**
     * The function breaks the input assigns them to variables and validates the input
     *
     * @param string $inputStore
     *            the line of input from the user
     */
    private function processInput($inputStore)
    {
        $inputArray = explode(" ", $inputStore);
        /* input columnas must be 3 to be valid input */
        if (count($inputArray) == 3) {
            $this->firstComputer = $inputArray[0];
            $this->finalComputer = $inputArray[1];
            if (! is_numeric($inputArray[2])) {
                echo "Latency not an Integer";
                $this->isInputProcessed = FALSE;
            }
            $this->requiredLatency = (int) $inputArray[2];
        } else {
            echo "Improper Input Format \n";
            $this->isInputProcessed = FALSE;
        }
    }

    /**
     * Checks if the search path is completed
     *
     * @return boolean true|false
     *         true if the program went through the search
     *         false if the input devices from the user were not found in the file
     *        
     */
    private function isSearchPathSuccessful()
    {
        $this->storeNodesAndConnections();
        if ($this->checkFileContainsUserInput()) {
            $this->assignNecessaryValues();
            $this->goToFinalComputer();
            return TRUE;
        } else {
            echo "Device From or Device To not present in the file";
            return FALSE;
        }
    }

    /**
     * this function stores the neighbours of all the individual computers
     * in an array called connectingComputers
     * gets unique list of devices and stores in the computers array
     */
    private function storeNodesAndConnections()
    {
        foreach ($this->computersAndLatency as $line) {
            /* pushing all the nodes to an array */
            array_push($this->computers, $line[0], $line[1]);
            /* storing the links of each computer in an array */
            $this->connectingComputers[$line[0]][] = array( 
                "connectedTo" => $line[1],
                "latency" => $line[2]
            );
            $this->connectingComputers[$line[1]][] = array(
                "connectedTo" => $line[0],
                "latency" => $line[2]
            );
        }
        /*Retrieved unique computer nodes*/
        $this->computers = array_unique($this->computers); 
        /*reassigns the value set to array so that the array does not have any empty value in the middle*/
        $this->computers = array_values($this->computers); 
    }

    /**
     *checks if the user provided a valid computername that is stored in the csv file
     * @return boolean True|false
     *         true if both the user input devices are present in the File
     *         false if one of the user input device is not present in the file
     */
    private function checkFileContainsUserInput()
    {
        for ($i = 0; $i < count($this->computers); $i ++) {
            if ($this->computers[$i] === $this->firstComputer) {
                $isFirstComputerPresent = TRUE;
            }
            if ($this->computers[$i] === $this->finalComputer) {
                $isSecondComputerPresent = TRUE;
            }
        }
        if ($isFirstComputerPresent and $isSecondComputerPresent) {
            
            return TRUE;
        } else {
            return FALSE;
        }
    }
/**
 * this function initiates the necessary values required for the search
 * sets shortest path to false wich makes the program run the first-path algorithm
 * sets the latency value of the firstComputer to 0 and the rest to infinity
 * sets the history visited computers of all devices to null
 */
    private function assignNecessaryValues()
    {
        foreach ($this->computers as $computer) {
            $this->latency[$computer] = INF;
            $this->previousComputer[$computer] = NULL;
        }
        $this->latency[$this->firstComputer] = 0;
        $this->unvisitedComputers = $this->computers;
        $this->combinedLatency = 0;
        $this->shortestPath = FALSE;
    }
    /**
     * The searching algorithm begins from this function
     * It selects an unvisited computer
     * Then it determines if the chosen computer is final computer and checks if it meets the required latency
     * if the required latency is not met, it goes to the shortest path algorith 
     * by setting the shortestPath flag to true
     * if the final computer is not found, it if calls a function to find the next connection
     */

    private function goToFinalComputer()
    {
        while (count($this->unvisitedComputers) > 0) {
            $this->goToUnvisitedComputer();
            if (($latency[$this->chosenComputer] == INF || $this->chosenComputer == $this->finalComputer)) {
                if ($this->requiredLatency >= $this->latency[$this->finalComputer]) {
                    break;
                } elseIf ($this->shortestPath == FALSE) {
                    $this->assignNecessaryValues();
                    $this->shortestPath = TRUE;
                    continue;
                    exit();
                } else
                    break;
            }
            $this->findAndStoreBestConnections();
        }
    }
    
/**
 * This function chooses a unvisited computer, by checking if its latency has been set or not
 * if not set the latency is infinity. The latency i set from the findAndStoreBestConnections function
 * then it resets the array of unvisited computers by removing the chosen computer from the list.
 */
    private function goToUnvisitedComputer()
    {
        $leastTime = INF;
        foreach ($this->unvisitedComputers as $unvisitedComputer) {
            if ($this->latency[$unvisitedComputer] < $leastTime) {
                $leastTime = $this->latency[$unvisitedComputer];
                $this->chosenComputer = $unvisitedComputer;
            }
        }
        $this->unvisitedComputers = array_diff($this->unvisitedComputers, array(
            $this->chosenComputer
        ));
    }
    
    /**
     * this is the function where the first path and the shortest path algorithms are implemented
     * It chooses each neighbour of the chosen computer 
     * and calculates the total latency to that neighbour from the first computer
     * For the first path algorithm, any neighbour that is not visited is chosen
     * The latency set to infinity determines that the neighbour has not been visited
     * For the sorting algorithm the neighbour with the least total latency is chosen
     * Both the algorithm set the latency of the selected neighbour which helps them to be chosen in the goToUnvisitedComputer function
     * the current chosen computer is also stored in the history of the selected computer as previousComputer
     */

    private function findAndStoreBestConnections()
    {
        if (isset($this->connectingComputers[$this->chosenComputer])) {
            foreach ($this->connectingComputers[$this->chosenComputer] as $connection) {
                $this->combinedLatency = $this->latency[$this->chosenComputer] + $connection["latency"];
                
                if ($this->latency[$connection["connectedTo"]] === INF && $this->shortestPath == FALSE) {
                    $this->latency[$connection["connectedTo"]] = $this->combinedLatency;
                    $this->previousComputer[$connection["connectedTo"]] = $this->chosenComputer;
                    /*After the shortest path is set to true, the visited list is emptied
                     * and all the connecting computers go through this algorithm*/
                } elseif ($this->combinedLatency < $this->latency[$connection["connectedTo"]] && $this->shortestPath == TRUE) {
                    $this->latency[$connection["connectedTo"]] = $this->combinedLatency;
                    $this->previousComputer[$connection["connectedTo"]] = $this->chosenComputer;
                }
            }
        }
    }
/**
 * This function prints the output line
 * if the required latency was not met, the function displays path  not found
 * if the latency has been met, The program stores the finalcomputer in an array
 * then it looks for the previous computer value of final computer 
 * previousComputer array helps to link back to the first computer which is all stored in an arry
 * Then this array is printed with the set latency value of the final computer
 * since it is the sum of all the latency values in the path
 */
    private function printOutput()
    {
        $path = array();
        echo "Output: ";
        if ($this->requiredLatency < $this->latency[$this->finalComputer]) {
            echo " Path not found";
        } else {
            $this->chosenComputer = $this->finalComputer;
            while (isset($this->previousComputer[$this->chosenComputer])) {
                array_unshift($path, $this->chosenComputer);
                $this->chosenComputer = $this->previousComputer[$this->chosenComputer];
            }
            array_unshift($path, $this->chosenComputer);
            echo $path[0];
            for ($i = 1; $i < count($path); $i ++) {
                echo " => " . $path[$i];
            }
            echo " => " . $this->latency[$this->finalComputer];
        }
    }
}
/*creating a new instance of networkPath*/
$networkPath = new networkPath(); 
$networkPath->starTest();
?>