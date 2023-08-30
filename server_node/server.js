const fs = require("fs");
const httpServer = require("https").createServer({
    key: fs.readFileSync(
        "/www/server/panel/vhost/cert/main-api-chat.rencity.vn/privkey.pem"
    ),
    cert: fs.readFileSync(
        "/www/server/panel/vhost/cert/main-api-chat.rencity.vn/fullchain.pem"
    ),
});

const options = {
    cors: {
        origin: "*",
    },
    allowEIO3: true,
};

const io = require("socket.io")(httpServer, options);

io.on("connection", (socket) => {
    console.log(
        socket.client.conn.server.clientsCount + " users connected " + socket.id
    );
    socket.on("chat:message_from_user_to_user", function (message) {
        console.log(message);
    });
});

httpServer.listen(6442);

var Redis = require("ioredis");
var redis = new Redis(6379);

redis.psubscribe("*", function (error, count) {
    console.log("Error:" + error);
});
//broadcastOn == chat (channel)
redis.on("pmessage", function (partner, channel, message) {
    try {
        if (channel == "laravel_database_chat" || chanel == "chat") {
            message = JSON.parse(message);

            if (message.event == "message_from_user") {
                var key =
                    "chat" +
                    ":" +
                    message.event +
                    ":" +
                    message.data.message.customer_id;
                var data = message.data.message;
                data.uread = message.data.unread;

                delete data.customer_id;

                if (message.data.message !== null) {
                    io.emit(key, data);
                }
            }

            if (message.event == "message_from_customer") {
                var key =
                    "chat" +
                    ":" +
                    message.event +
                    ":" +
                    message.data.message.customer_id;
                var data = message.data.message;
                data.uread = message.data.unread;

                delete data.customer_id;

                if (message.data.message !== null) {
                    io.emit(key, data);

                    console.log(
                        "sent " + key + "   unread " + message.data.unread
                    );
                }

                io.emit("chat:message_from_customer", {
                    uread: message.data.unread,
                });
            }
            if (message.event == "message_from_user_to_user") {
                var key =
                    "chat" +
                    ":" +
                    message.event +
                    ":" +
                    message.data.message.user_id +
                    ":" +
                    message.data.message.vs_user_id;
                let key2 = "chat" + ":" + message.event + ":" + 8;
                var data = message.data.message;
                data.uread = message.data.unread;

                delete data.user_id;

                if (message.data.content !== null) {
                    io.emit(key, data);
                    console.log(
                        "sent " + key + "   unread " + message.data.unread
                    );
                }

                io.emit(key, { uread: message.data.unread });
            }

            //badges
            if (message.event == "badges_user") {
                var key =
                    "badges" + ":" + message.event + ":" + message.data.user_id;
                var data = message.data.data_badges;

                if (data !== null) {
                    io.emit(key, data);

                    console.log(
                        "sent " + key + "   unread " + message.data.unread
                    );
                }
            }
            if (message.event == "badges_customer") {
                var key =
                    "badges" +
                    ":" +
                    message.event +
                    ":" +
                    message.data.customer_id;
                var data = message.data.data_badges;

                if (data !== null) {
                    io.emit(key, data);

                    console.log(
                        "sent " + key + "   unread " + message.data.unread
                    );
                }
            }
        }
    } catch (err) {
        console.log(err);
    }
});
