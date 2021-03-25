<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Emil-Shake</title>
	<!-- css bootstrap -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css">

	<link rel="stylesheet" type="text/css" href="test.css">


	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

	<script type="text/javascript">

		window.onload = function()
		{
			disableVerificationIcons(true,true);
		}

		async function emailVerifierCall(name, domain)
		{
			var completeEmail = name + "@" + domain;

			completeEmail = encodeURIComponent(completeEmail);

			let response = await fetch("https://email-checker.p.rapidapi.com/verify/v1?email=" + completeEmail, {
				"method": "GET",
				"headers": {
					"x-rapidapi-key": "463a4a02abmsh984ef77b4d06200p160a31jsn324e0850e088",
					"x-rapidapi-host": "email-checker.p.rapidapi.com"
				}
			});

			let data = await response.json();

			return data;

			
		}

		function grammarbot()
		{
			text = encodeURI(document.getElementById("textareaRaw").value);

			const data = "text="+ text + "&language=en-US";

			const xhr = new XMLHttpRequest();
			xhr.withCredentials = true;

			xhr.addEventListener("readystatechange", function () {
				if (this.readyState === this.DONE) {
					var response = JSON.parse(this.responseText);
					verifyText(response);
				}
			});

			xhr.open("POST", "https://grammarbot.p.rapidapi.com/check");
			xhr.setRequestHeader("content-type", "application/x-www-form-urlencoded");
			xhr.setRequestHeader("x-rapidapi-key", "463a4a02abmsh984ef77b4d06200p160a31jsn324e0850e088");
			xhr.setRequestHeader("x-rapidapi-host", "grammarbot.p.rapidapi.com");

			xhr.send(data);
		}

		function verifyText(response)
		{

			var textNotCorrected = document.getElementById("textareaRaw").value;

			textNotCorrected += " ";

			var textCorrected = textNotCorrected;

			var isEmpty = inputIsEmpty();
			if(!isEmpty[0] && window.globalSenderData != undefined)
			{
				if(globalSenderData.status == "valid")
				{
					var inputSenderDomain = document.forms["myForm"]["senderDomain"].value;
					var regex = new RegExp('(^[a-z]+)[^.]');
					var domainM =  inputSenderDomain.replace(/([.])\w+/i,"");

					var mistakes = response.matches.length;
					$.post('serveur.php',
					{
						mistakesCount: mistakes,
						domainMistake : domainM
					}, function(data) {
					});
				}
				
			}
			
			for(var i = response.matches.length-1; i >= 0; i--)
			{
				var correctedWord = response.matches[i].replacements[0].value

				var indexWordTobeCorrectedBegin = response.matches[i].context.offset;

				var indexWordTobeCorrectedEnd = textNotCorrected.indexOf(" ", indexWordTobeCorrectedBegin);

				var wordToBeCorrected = textNotCorrected.substr(indexWordTobeCorrectedBegin, indexWordTobeCorrectedEnd - indexWordTobeCorrectedBegin);

				wordToBeCorrected = wordToBeCorrected.trim();

				textCorrected = textNotCorrected.replace(wordToBeCorrected, correctedWord);

				textNotCorrected = textCorrected;
			}

			document.getElementById("textC").value = textCorrected;
		}

		function checkDomain()
		{
			var isEmpty = inputIsEmpty();
			var inputSenderName = document.forms["myForm"]["senderName"].value;
			var inputSenderDomain = document.forms["myForm"]["senderDomain"].value;
			var inputReceiverName = document.forms["myForm"]["receiverName"].value;
			var inputReceiverDomain = document.forms["myForm"]["receiverDomain"].value;
			var iconsToChange = [true,true];

			disableVerificationIcons(isEmpty[0],isEmpty[1]);
			if(!isEmpty[0])
			{
				emailVerifierCall(inputSenderName,inputSenderDomain).then((senderData) => {

					window.globalSenderData = senderData;
					if(senderData.status == "valid")
					{
						iconsToChange[0] = true;
						var regex = new RegExp('(^[a-z]+)[^.]');
						var domainToSend =  inputSenderDomain.replace(/([.])\w+/i,"");

						$.post('serveur.php',
						{
							domain: domainToSend
						}, function(data) {
						});
					}
					else
					{
						iconsToChange[0] = false;
					}
					changeVerificationIcons(iconsToChange[0],iconsToChange[1]);

				}, (raison) => {
					console.log(raison);
				});
				
			}

			if(!isEmpty[1])
			{
				emailVerifierCall(inputReceiverName,inputReceiverDomain).then((receiverData) => {
					if(receiverData.status == "valid")
					{
						iconsToChange[1] = true;
					}
					else
					{
						iconsToChange[1] = false;
					}
					changeVerificationIcons(iconsToChange[0],iconsToChange[1]);

				}, (raison) => {
					console.log(raison);
				});
			}

			
		}

		function disableVerificationIcons(isEmptySender,isEmptyReceiver)
		{
			iconsClassNameTab = ["emailFrom","emailTo"];
			var iconSender = document.getElementsByClassName(iconsClassNameTab[0]);
			var iconReceiver = document.getElementsByClassName(iconsClassNameTab[1]);

			if(isEmptySender)
			{
				iconSender[0].style.visibility = "hidden";
			}
			else
			{
				iconSender[0].style.visibility = "visible";
			}

			if(isEmptyReceiver)
			{
				iconReceiver[0].style.visibility = "hidden";
			}
			else
			{
				iconReceiver[0].style.visibility = "visible";
			}	
		}

		function inputIsEmpty()
		{
			var inputSenderName = document.forms["myForm"]["senderName"].value;
			var inputSenderDomain = document.forms["myForm"]["senderDomain"].value;
			var inputReceiverName = document.forms["myForm"]["receiverName"].value;
			var inputReceiverDomain = document.forms["myForm"]["receiverDomain"].value;
			var isEmpty = [false,false];


			if(inputSenderName == "" || inputSenderDomain == "")
			{
				isEmpty[0] = true;
			}

			if(inputReceiverName == "" || inputReceiverDomain == "")
			{
				isEmpty[1] = true;
			}

			return isEmpty;

		}

		function copyToClipBoard()
		{
			var icon = document.getElementsByClassName("copyIcon");

			var iconToAdd = "bi-stickies-fill";
			var iconToRemove = "bi-sticky";

			icon[0].classList.remove(iconToRemove);
			icon[0].classList.add(iconToAdd);


			label = document.getElementsByClassName("text-muted");
			label[0].innerHTML = "copied!";

			let textarea = document.getElementById("textC");
			textarea.select();
			document.execCommand("copy");

		}

		function changeVerificationIcons(senderCorrect = true, receiverCorrect = true)
		{
			var iconSender = document.getElementsByClassName("emailFrom");
			var iconReceiver = document.getElementsByClassName("emailTo");

			var iconToAddSender = "bi-x-circle-fill";
			var iconToRemoveSender = "bi-check-circle-fill"

			var iconToAddReceiver = "bi-x-circle-fill";
			var iconToRemoveReceiver = "bi-check-circle-fill"

			if(senderCorrect)
			{

				iconToAddSender = "bi-check-circle-fill";
				iconToRemoveSender = "bi-x-circle-fill";
			}
			if(receiverCorrect)
			{
				iconToAddReceiver = "bi-check-circle-fill";
				iconToRemoveReceiver = "bi-x-circle-fill";
			}

			iconSender[0].classList.remove(iconToRemoveSender);
			iconSender[0].classList.add(iconToAddSender);

			iconReceiver[0].classList.remove(iconToRemoveReceiver);
			iconReceiver[0].classList.add(iconToAddReceiver);

		}
		
	</script>
</head>
<body>
	<div class="container-fluid">
		<div class="row justify-content-md-center">
			<div class="col-md-5">
				<form name="myForm">
					<div class="input-group mb-3">
						<span class="input-group-text" id="tbnh">From :</span>
						<input type="text" class="form-control" placeholder="name" aria-label="Username" id="senderName" value="arthur.vidalenc">
						<span class="input-group-text">@</span>
						<input type="text" class="form-control" placeholder="Domain" aria-label="Server" id="senderDomain" value="gmail.com">
						<i class="bi icones emailFrom bi-check-circle-fill" style="font-size: 2.2em; visibility: visible;"></i>
					</div>
					<div class="input-group mb-3">
						<span class="input-group-text" id="tbnh">To :</span>
						<input type="text" class="form-control" placeholder="name" aria-label="Username" id="receiverName">
						<span class="input-group-text">@</span>
						<input type="text" class="form-control" placeholder="Domain" aria-label="Server" id="receiverDomain">
						<i class="bi icones emailTo bi-check-circle-fill" style="font-size: 2.2em; visibility: visible;"></i>
					</div>
					<div class="input-group mb-3">
						<span class="input-group-text" id="tbnh">Subject :</span>
						<input type="text" class="form-control" placeholder="Subject" aria-label="Subject">
					</div>

					<button type="button" class="btn btn-primary mr-4" onclick="checkDomain()" >check domains</button>
					<button type="button" class="btn btn-primary ml-4" style="float: right;" onclick="grammarbot()">check grammar</button>
				</form>

				<div class="col-sm">
					<label for="text">text :</label>
					<textarea class="form-control" id="textareaRaw" rows="3" value="">Susan go to the sttore everryday </textarea>
				</div>

				<a href="Documentation.html" class="btn btn-warning" role="button">Documentation</a>
				
			</div>
			<div class="col-md-1"></div>
			<div class="col-md-5">
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th scope="col">email</th>
							<th scope="col">occurences</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>gmail</td>
							<td>20003</td>
						</tr>
						<tr>
							<td>yahoo</td>
							<td>12458</td>
						</tr>
					</tbody>
				</table>

				<div class="col-sm mt-6">
					<label for="text">Correction :</label>
					<textarea class="form-control" id="textC" rows="3"></textarea>
				</div>
				<div class="d-flex flex-row-reverse">
					<div class="p-2">
						<button class="btn" onclick="copyToClipBoard()"><i class="bi bi-sticky copyIcon"></i></button>
						<p class="text-muted small">copy to clipboard</p>
					</div>
					
				</div>
				
			</div>

			
		</div>
	</div>
	
	<!-- JS bootstrap -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>



</body>
</html>