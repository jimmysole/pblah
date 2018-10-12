function chatMain() {
	this.update = updateChat;
	this.send = sendChat;
	this.getChatState = getStateOfChat;
}


function getStateOfChat() {
	if (!instance) {
		instance = true;
		
		$.ajax({
			type: "POST",
			url: "/members/chat/get-chat-state",
			data: { 'state' : 'getChatState', 'file' : file},
			dataType: "json",
			success: function(data) {
				state = data.state;
				instance = false;
			}
		});
	}
}


function updateChat() {
	if (!instance) {
		instance = true;
		
		$.ajax({
			type: "POST",
			url: "/members/chat/update-chat",
			data: { 'function' : 'update', 'state' : state, 'file' : file },
			dataType: "json",
			success: function(data) {
				if (data.text) {
					for (var i = 0; i < data.text.length; i++) {
						$('#chat-section').append($("" + data.text[i] + ""));
					}
				}
				
				document.getElementById('chat-section').scrollTop = document.getElementById('chat-section').scrollHeight;
				instance = false;
				state = data.state;
			}
		});
	} else {
		setTimeout(updateChat, 1500);
	}
}


function sendChat(message) {
	updateChat();
	
	$.ajax({
		type: "POST",
		url: "/members/chat/send-message",
		data: { 'function' : 'send', 'message' : message, 'file' : file},
		dataType: "json",
		success: function(data) {
			updateChat();
		}
	});
}