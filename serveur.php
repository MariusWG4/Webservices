<?php

$strJsonFileContents = file_get_contents("bd.json");
$json = json_decode($strJsonFileContents);

if(empty($_GET))
{

	echo json_encode( $json->domain );
}

if(isset($_GET['domain']))
{
	$domain = htmlentities($_GET['domain']);

	if(isset($_GET['mistakes']))
	{
		foreach ($json->domain as $JDomain)
		{
			if($JDomain->domain == $domain)
			{
				foreach ($json->mistakes as $JMistakes)
				{
					if($JMistakes->domain == $domain)
					{
						$jsonToSend["domain"] = $domain;
						if($JDomain->occurrence > $JMistakes->mistakesCount)
						{
							$jsonToSend["averageMistakesPerMail"] = round($JDomain->occurrence / $JMistakes->mistakesCount);
						}
						else
						{
							$jsonToSend["averageMistakesPerMail"] = round($JMistakes->mistakesCount / $JDomain->occurrence);
						}

						echo json_encode($jsonToSend);
					}
				}
			}
		}
	}
	else
	{
		foreach ($json->domain as $element) {
			if($element->domain == $domain)
			{
				echo json_encode( $element );
				break;
			}
		}


		if($json->domain[0]->domain == $domain)
		{
			$json->domain[0]->occurrence += 1;
		}
	}
}

if(isset($_GET['mistakes']))
{
	$i = 0;
	foreach ($json->domain as $JDomain)
	{
		foreach ($json->mistakes as $JMistakes)
		{
			if($JMistakes->domain == $JDomain->domain)
			{
				$jsonToSend[$i]["domain"] = $JDomain->domain;
				if($JDomain->occurrence > $JMistakes->mistakesCount)
				{
					$jsonToSend[$i]["averageMistakesPerMail"] = round($JDomain->occurrence / $JMistakes->mistakesCount);
				}
				else
				{
					$jsonToSend[$i]["averageMistakesPerMail"] = round($JMistakes->mistakesCount / $JDomain->occurrence);
				}

				echo json_encode($jsonToSend);
			}
		}
	}
}

if(isset($_POST["domain"]))
{
	$domain = htmlentities($_POST['domain']);
	$exists = false;
	foreach ($json->domain as $element) {
		if($element->domain == $domain)
		{
			$element->occurrence += 1;
			$exists = true;
			break;
		}
	}

	if(!$exists)
	{
		$objectToAdd["domain"] = $domain;
		$objectToAdd["occurrence"] = 1;
		array_push($json->domain, $objectToAdd);
		$exists = false;
	}

	foreach ($json->mistakes as $element) {
		if($element->domain == $domain)
		{
			$exists = true;
			break;
		}
	}
	if(!$exists)
	{
		$objectToAdd2["domain"] = $domain;
		$objectToAdd2["mistakesCount"] = 0;
		array_push($json->domain, $objectToAdd2);
	}
}

if(isset($_POST["mistakesCount"]))
{
	$mistakesCount = $_POST["mistakesCount"];
	$domainMistake =  $_POST["domainMistake"];
	foreach ($json->mistakes as $element) {
		if($element->domain == $domainMistake)
		{
			$element->mistakesCount += $mistakesCount;
			break;
		}
	}
}

if(!empty($_POST))
{
	$finalJson = json_encode($json);

	file_put_contents("bd.json", $finalJson);
}


?>
