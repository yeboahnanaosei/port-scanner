#!/usr/bin/env php
<?php
try {
    // $argc = the number of options supplied at the command prompt
    // $argv = the values of the options supplied at the command prompt
    $numOpenPorts = start($argc, $argv);
    report(scriptExecutionTime(), $numOpenPorts);
} catch (\Throwable $e) {
    echo "Error: ", $e->getMessage(), " Line: {$e->getLine()}";
}


/**
 * Starts the script. It takes the arguments supplied at
 * the commandline and uses them to run the script
 *
 * @param integer $argc The number of arguments supplied
 * @param array $argv An array of the arguments supplied
 * @return integer $totalOpenPorts The total number of open ports found
 */
function start(int $argc, array $argv) : int
{
    // Show a message if the number of supplied arguments
    // are not adequate
    if ($argc < 4) {
        showTip();
        exit(1);
    }

    $host = gethostbyname($argv[1]);
    $startPort = (int)$argv[2];
    $endPort = (int)$argv[3];
    $ports = getPortNumbers($startPort, $endPort);
    $totalOpenPorts = 0;

    foreach ($ports as $port) {
        if (isPortOpen($host, $port)) {
            $totalOpenPorts++;
            printf("Open:\t%s:%s\n", $port, getPortName($port));
        }
    }

    return $totalOpenPorts;
}

/**
 * Shows a tip on how to use this script
 *
 * @return void
 */
function showTip()
{
    // Get the name of this file
    $scriptName = basename(realpath(__FILE__));
    echo <<<TIP
    Usage
    =====
    You will have to supply 3 things in this order:
    php {$scriptName} [HOST] [START_PORT] [END_PORT]

    Example:
    =======
    php {$scriptName} localhost 1 3500

TIP;
}

/**
 * Checks to see if a particular port is open
 *
 * @param string $host The name of the host on which to check the port
 * @param integer $port The port number you want to check
 * @return boolean Returns true if port is open. False otherwise
 */
function isPortOpen(string $host, int $port) : bool
{
    // Create a socket and use it to test the ports
    // Suppress all PHP warning with the "@" error
    // suppressor
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    $conn = @socket_connect($socket, $host, $port);


    if ($conn) {
        socket_close($socket);
        return true;
    } else {
        return false;
    }
}

/**
 * This is a generator. It yields one port between the start and
 * end ports
 *
 * @param integer $start The port to start scanning from
 * @param integer $end The port to end scanning
 */
function getPortNumbers(int $start, int $end)
{
    while ($start <= $end) {
        yield $start++;
    }
}


/**
 * Retrieves the name of the service used by a particular port
 *
 * @param string $portNumber A port number
 * @param array $commonPortNames An associative array of common port numbers
 * and their associated service names
 * @return string $serviceName The name of the service using the port.
 */
function getPortName(string $portNumber) : string
{
    static $commonPortNames = [
        '21' => 'FTP',
        '22' => 'SSH',
        '23' => 'TELNET',
        '25' => 'SMTP',
        '53' => 'DNS',
        '69' => 'TFTP',
        '80' => 'HTTP',
        '109' => 'POP2',
        '110' => 'POP3',
        '123' => 'NTP',
        '137' => 'NETBIOS-NS',
        '138' => 'NETBIOS-DGM',
        '139' => 'NETBIOS-SSN',
        '143' => 'IMAP',
        '156' => 'SQL-SERVER',
        '389' => 'LDAP',
        '443' => 'HTTPS',
        '546' => 'DHCP-CLIENT',
        '547' => 'DHCP-SERVER',
        '631' => 'CUPS-SERVER',
        '995' => 'POP3-SSL',
        '993' => 'IMAP-SSL',
        '2086' => 'WHM/CPANEL',
        '2087' => 'WHM/CPANEL',
        '2082' => 'CPANEL',
        '2083' => 'CPANEL',
        '3306' => 'MYSQL',
        '5432' => 'POSTGRESQL',
        '8443' => 'PLESK',
        '10000' => 'VIRTUALMIN/WEBMIN',
    ];

    if (array_key_exists($portNumber, $commonPortNames)) {
        return $commonPortNames[$portNumber];
    } else {
        return "Unknown service";
    }
}


/**
 * Calculates how long (in seconds) the script took to run
 *
 * @return float Returns the number of seconds the script took to run
 */
function scriptExecutionTime() : float
{
    $startTime = $_SERVER['REQUEST_TIME_FLOAT'];
    $endTime = microtime(true);
    return (float)number_format($endTime - $startTime, 2);
}

/**
 * Reports to the screen the total time (in seconds) the script took to run
 * as well as the number of open ports found.
 *
 * @param float $executionTime The amount of time the script took to complete
 * @param integer $numOpenPorts The number of open ports found
 * @return void
 */
function report(float $executionTime, int $numOpenPorts)
{
    $format = <<<FMT

=================================
        Scan Report !!!
=================================
Scan completed in: %1\$.2f seconds
Number of open ports: %2\$s

FMT;
    printf($format, $executionTime, $numOpenPorts);
}
