          


	******INSTRUCTIONS TO RUN THE PHP FILE IN COMMAND LINE******

=> From the command-line navigate to the folder the php file is in
=> Type "php filename.php" and press Enter. In this case, Since the filename is networkPath type "php networkPath.php"
=> The program starts running and asks for the csv file. Type in the name of csv file to read the device connections and latency from. A test csv file named connections.csv has been included. If the file is not found or cant be read, error is displayed
=>Then the program asks for input. Enter the input in the format "DeviceFrom DeviceTo Latency(milliseconds)". If the format is wrong the program gives an error of "Improper input format"
=>The program then gives the possible path if it has latency less than or equal to the provided latency. The provided latency defines the path
For example:

Input: A F 1200
Output: A => B => D => E => F => 1120
Input: A F 1100
Output: A => C => D => E => F => 1060

=>After the output the program keeps asking for another input until "quit" is typed.

                         ******  Algorithm  *****
=>The program first looks for any possible path and finds out the latency needed to reach there. If the latency is smaller than the provided latency, the program prints the output. But if the latency is greater than the provided latency, the program calculates the shortest path then prints out the output if a path is found.