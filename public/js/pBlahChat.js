
var chatInstance = false;
var stateOfChat;
var message;

function pBlahChat() {
	this.update = updateChat;
	this.send = sendMessage;
	this.getState = getStateOfChat;
}


function getStateOfChat() {
	if (!chatInstance) {
		chatInstance = true;
		
		$.post('/members/chat/process-chat', { 'function' : 'getState' }, function(data) {
			stateOfChat = data.state;
			instance = false;
		});
	}
}

function updateChat() {
	if (!chatInstance) {
		$.post('/members/chat/process-chat', { 'function' : 'update', 'state' : stateOfChat }, function(data) {
			if (data.text) {
				console.log(data.text);
				for (var i = 0; i < data.text.length; i++) {
					$('#chat-area').append(data.text[i]);
				}
			} else {
				console.log(data.text);
			}
			
			document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
			
			chatInstance = false;
			
			state = data.state;
		});
	} else {
		setTimeout(updateChat, 1500);
	}
}


function sendMessage(to, message) {
	updateChat();
	
	$.post('/members/chat/process-chat', { 'function' : 'send', 'to' : to, 'message' : message }, function(data) {
		updateChat();
		$('#message').val("");
	}, "json");
}