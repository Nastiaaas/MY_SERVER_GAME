
const ws = new WebSocket("ws://" + currecntIp + ":8080?session_id=" + sessionId);

ws.onopen = function(e) {
    console.log("Websocket good!");
};

ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    console.log("message: ", event.data);
    arrangeData(data);
};

ws.onerror = function(error) {
    console.error("Error.");
};

ws.onclose = function(event) {
    console.log("Connection closed. code:", event.code);
};



let pos = {x:49.5, y:48.5};
let currentUser = {isHunter:false, username:"YOU", playerId: undefined};
let otherUsers = [];

// Function that gets activated by ws.onmessage
function arrangeData(inputDataObject) {
    if (inputDataObject.type === 'ping') return;
    if (inputDataObject.length === undefined) {
        if (inputDataObject.username == CURRENT_USER_NAME) {
            currentUser.isHunter = inputDataObject.isHunter;
            currentUser.playerId = inputDataObject.playerId;
            pos.x = inputDataObject.x;
            pos.y = inputDataObject.y;
            otherUsers[inputDataObject.playerId] = {username: undefined};
        } else {
            if (inputDataObject.username === undefined){
                otherUsers[inputDataObject.playerId] = {username: undefined};
            } else {
                otherUsers[inputDataObject.playerId] = inputDataObject;
            }
        }
    } else {
        for(let i = 0; i < inputDataObject.length; ++i){
            if (inputDataObject[i].username == CURRENT_USER_NAME) {
                currentUser.isHunter = inputDataObject[i].isHunter;
                currentUser.playerId = inputDataObject[i].playerId;
                pos.x = inputDataObject[i].x;
                pos.y = inputDataObject[i].y;
                otherUsers[inputDataObject[i].playerId] = {username: undefined}
            } else {
                if (inputDataObject[i].username === undefined){
                    otherUsers[inputDataObject[i].playerId] = {username: undefined}
                } else {
                    otherUsers[inputDataObject[i].playerId] = inputDataObject[i];
                }
            }
        }
    }
}

//Output gets sent to ws.send
function sendData() {
    outputData = {x:pos.x, y:pos.y, username:CURRENT_USER_NAME, playerId: currentUser.playerId};
    console.log(outputData);

    if(ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify(outputData));
    }
    // transmit output data via vss
}

//Removes the player from /game.php(.php) if they are not connected to the server
function onDisconnect(){
    window.location.href = "/";
}

//arrangeData({x:49.5, y:47.5, isHunter:false, username:"YOU", playerId: 3});

//arrangeData({x:0, y:0, isHunter:false, username:undefined, playerId: 0});