<html>
<head><title>Calculator</title></head>
<body>

<h1>Calculator</h1>
(10/5/2016 by Jun Zhou)<br />
Type an expression in the following box (e.g., 10.5+20*3/25).
<p>
    <form method="GET">
        <input type="text" name="expr">
        <input type="submit" value="Calculate">
    </form>
</p>

<ul>
    <li>Only numbers and +,-,* and / operators are allowed in the expression.
    <li>The evaluation follows the standard operator precedence.
    <li>The calculator does not support parentheses.
    <li>The calculator handles invalid input "gracefully". It does not output PHP error messages.
</ul>

Here are some(but not limit to) reasonable test cases:
<ol>
  <li> A basic arithmetic operation:  3+4*5=23 </li>
  <li> An expression with floating point or negative sign : -3.2+2*4-1/3 = 4.46666666667, 3*-2.1*2 = -12.6 </li>
  <li> Some typos inside operation (e.g. alphabetic letter): Invalid input expression 2d4+1 </li>
</ol>

<?php
  if ($_GET["expr"])
  {
    $input = $_GET["expr"];
    echo "<h2>Result</h2>";

    //Regex explanation:
    // start with possibly negative expression: ^-?
    // next, some number where int may not start with 0, unless is zero:  (0|[1-9]\d*)
    // or decimal which must start with integer followed by dot and digits : ((0|[1-9]\d*)\.\d+)
    // => in summary, a number can be represented as: -?((0|[1-9]\d*)|((0|[1-9]\d*)\.\d+))
    // (deals with all cases: 00 invalid, -0 valid, -0.3 valid, 00.3 invalid, -03 invalid, 53 valid, 53.02 valid)
    // optional whitespace: (\s*)
    // so after initial number, must follow by operator then number
    // operator: [+\-*/]
    // optional whitespace: (\s*)
    // then required number again: -?((0|[1-9]\d*)|((0|[1-9]\d*)\.\d+))
    // => in summary, an expression can be represented as operator then number [+\-*\/](\s*)-?((0|[1-9]\d*)|((0|[1-9]\d*)\.\d+))(\s*))
    // expressions may be repeated 0 or more times: *
    // then nothing following, cannot end on something invalid: $
    // => the whole regex: ^-?((0|[1-9]\d*)|((0|[1-9]\d*)\.\d+))(\s*)([+\-*\/](\s*)-?((0|[1-9]\d*)|((0|[1-9]\d*)\.\d+))(\s*))*$
    // then /x to ignore whitespace
    if ( preg_match("/^-?((0|[1-9]\d*)|((0|[1-9]\d*)\.\d+))(\s*)([+\-*\/](\s*)-?((0|[1-9]\d*)|((0|[1-9]\d*)\.\d+))(\s*))*$/", $_GET["expr"]) )
    {
      //ob_start();
      $input = str_replace("--","- -",$_GET["expr"]); //handle special case: 5--5 must parse as 5 - -5
      $success = eval("\$ans = $input ;"); //check regex before eval, otherwise eval user input is dangerous, security issue
      //$error = ob_get_clean();

      //if ($error)
      //echo $error . "<br>";
      
      //some parsing error, maybe something missed by regex?
      if ( $success === false )
        echo "Invalid Expression!";
      ////division by zero error
      //elseif (strpos($error, 'zero')!==false)
      //{
      //  echo "Division by zero error!";
      //}
      
      //Regex explanation:
      //division \/
      //any number of whitespaces: (\s*)
      //zero with no decimal after: (?!\.)
      elseif (preg_match("/\/(\s*)0(?!\.)/",$input))
        echo "<p>Division by zero error!</p>";
      else
      {
        $result = $input . " = " . $ans;
        echo "<p>$result</p>";
      }
    }
    else
      echo "Invalid Expression!";
  }
?>

</body>
</html>