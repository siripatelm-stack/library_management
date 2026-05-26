<?php
session_start();

if(!isset($_SESSION['admin_username'])){
header("Location: admin_login.php");
exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>AI Chatbot</title>

<style>

body{
font-family:Arial;
background:#f5f5f5;
margin:0;
padding:0;
}

.chatbot-container{
width:350px;
background:#fff;
border-radius:10px;
box-shadow:0 0 10px rgba(0,0,0,0.1);
overflow:hidden;
position:fixed;
bottom:20px;
right:20px;
z-index:999;
}

.chat-header{
background:#4f6df5;
color:#fff;
padding:15px;
font-size:18px;
font-weight:bold;
}

.chat-box{
height:350px;
padding:10px;
overflow-y:auto;
background:#f5f5f5;
}

.bot-message,
.user-message{
padding:10px;
margin:10px 0;
border-radius:10px;
max-width:80%;
word-wrap:break-word;
}

.bot-message{
background:#e0e7ff;
}

.user-message{
background:#4f6df5;
color:#fff;
margin-left:auto;
}

.chat-input{
display:flex;
border-top:1px solid #ccc;
}

.chat-input input{
flex:1;
padding:10px;
border:none;
outline:none;
}

.chat-input button{
background:#4f6df5;
color:#fff;
border:none;
padding:10px 15px;
cursor:pointer;
}

</style>

</head>

<body>

<div class="chatbot-container">

<div class="chat-header">
AI Assistant
</div>

<div class="chat-box" id="chatBox">

<div class="bot-message">
Hello 👋 Ask me library related questions.
</div>

</div>

<div class="chat-input">

<input
type="text"
id="userInput"
placeholder="Type your message..."
>

<button onclick="sendMessage()">
Send
</button>

</div>

</div>

<script>

function addMessage(message,type){

const chatBox=
document.getElementById("chatBox");

const div=
document.createElement("div");

div.className=
type+"-message";

div.innerHTML=message;

chatBox.appendChild(div);

chatBox.scrollTop=
chatBox.scrollHeight;

}

function sendMessage(){

const input=
document.getElementById("userInput");

const message=
input.value.trim();

if(message===""){
return;
}

addMessage(message,"user");

input.value="";

fetch('http://127.0.0.1:5000/chat',{

method:'POST',

headers:{
'Content-Type':'application/json'
},
body:JSON.stringify({
message:message
})
})
.then(response=>response.json())
.then(data=>{
if(data.reply){
addMessage(data.reply,'bot');
}
else if(data.error){
addMessage(data.error,'bot');
}
})
.catch(error=>{
addMessage(
"Server Error",
'bot'
);
});
}
</script>
</body>
</html>