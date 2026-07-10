@extends('backend.layouts.app')

@section('content')

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card chat-app">
                <div class="row">
                    <div id="plist" class="col-2 people-list">
                        
                        {{-- <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                            </div>
                            <input type="text" id="search-input" class="form-control" placeholder="Search...">
                        </div> --}}

                        <div class="filter_btn pl-3 pb-1">
                            <button class="btn btn-primary btn-sm all_user">All</button>
                            <button class="btn btn-success btn-sm unread_user">Unread( <span class="unread_count">0</span> )</button>
                        </div>

                        <div class="all_user_list">
                            <ul class="list-unstyled style-4 chat-list mt-2 mb-0 usersList"> </ul>
                            <div class="loadmore text-center">
                                <button class="btn btn-primary btn-sm text-light m-4">Load more</button>
                            </div>
                        </div>
                        
                        <div class="unread_user_list" style="display: none">
                            <ul class="list-unstyled style-4 chat-list mt-2 mb-0 unread_users_List"> </ul>
                            <div class="unread_loadmore text-center">
                                <button class="btn btn-primary btn-sm text-light m-4">Load more</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 chat p-0"> 
                        {{-- header show section --}}
                        <div class="chat-header clearfix">
                            <div class="row">
                                <div class="col-lg-6">
                                    <a href="javascript:void(0);" data-toggle="modal" data-target="#view_info">
                                        <img src="{{ static_asset('assets/img/female-and-male-icon.jpg') }}" alt="avatar">
                                    </a>
                                    <div class="chat-about">
                                        <h6 class="m-b-0 mb-0 user_name_top"></h6>
                                        <small>user online</small>
                                        {{-- <small>Last seen: 2 hours ago</small> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- message conversion section --}}
                        <div class="loadmore2 d-none-transion">
                            <button class="btn btn-secondary d-block w-100"> Load more</button>
                        </div>
                        <div class="chat-history style-4">
                            {{-- load more chat button --}}
                            <ul class="m-b-0 style-4 allConversactions push_chat"></ul>
                        </div>

                        {{-- message send section --}}
                        <div class="chat-message clearfix">
                            <form action="" id="sendSmsForm" onsubmit="return false">
                                <div class="input-group mb-0">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="lab la-telegram-plane"></i></span>
                                    </div>
                                    
                                    <input type="text" id="message" class="form-control"  placeholder="Enter text here...">

                                    <div class="input-group-prepend">
                                        <button type="submit" class="input-group-text">Send</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="others" class="col-4 bg-light">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        .list-unstyled{
            overflow-y: auto;
            height: 74vh;
        }
        .d-none-transion{
            display: none;
            transition: all 0.4s linear;
        }
        .adminTxtBg{
            color: #fff !important;
            background-color: #0078FF !important;
        }
        .chat-app{
            height: 90vh;
            margin-bottom: 20px;
        }

        .style-4::-webkit-scrollbar-track{
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
            background-color: #F5F5F5;
            border-radius: 10px;
        }

        .style-4::-webkit-scrollbar{
            width: 6px;
            background-color: #F5F5F5;
        }

        .style-4::-webkit-scrollbar-thumb{
            background-color: grey;
            border-radius: 10px;
        }

        .card {
            background: #fff;
            transition: .5s;
            border: 0;
            margin-bottom: 30px;
            border-radius: .55rem;
            position: relative;
            width: 100%;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
        }
        .chat-app .people-list {
            padding: 15px 0 15px 15px;
        }

        .chat-app .chat {
            border-left: 1px solid #eaeaea;
            border-right: 1px solid #eaeaea;
            height: 100%;
        }

        .people-list {
            -moz-transition: .5s;
            -o-transition: .5s;
            -webkit-transition: .5s;
            transition: .5s
        }

        .people-list .chat-list li {
            padding: 10px 15px;
            list-style: none;
            border-radius: 3px
        }

        .people-list .chat-list li:hover {
            background: #efefef;
            cursor: pointer
        }

        .people-list .chat-list li.active {
            background: #efefef
        }

        .people-list .chat-list li .name {
            font-size: 15px
        }

        .people-list .chat-list img {
            margin-top: 3px;
            width: 45px;
            border-radius: 50%;
            border: 1px solid darkgray;
        }

        .people-list img {
            float: left;
            border-radius: 50%;
            border: 1px solid darkgray;
        }

        .people-list .about {
            float: left;
            padding-left: 8px
        }
        .about .name{
            text-transform: capitalize;
        }

        .people-list .status {
            color: #999;
            font-size: 13px
        }

        .chat .chat-header {
            padding: 15px 20px;
            border-bottom: 2px solid #f4f7f6;
            display: none;
        }

        .chat .chat-header img {
            float: left;
            border-radius: 40px;
            width: 40px;
            border: 1px solid darkgray;
        }

        .chat .chat-header .chat-about {
            float: left;
            padding-left: 10px
        }

        .chat .chat-history {
            padding: 20px;
            border-bottom: 2px solid #fff;
            /* min-height: 100vh; */
            overflow:auto;
            display: flex;
            flex-direction: column-reverse;
            scroll-behavior: smooth;
            height: 74vh;
        }
        
        .chat .chat-history ul {
            padding: 0
        }

        .chat .chat-history ul li {
            list-style: none;
            margin-bottom: 30px
        }

        .chat .chat-history ul li:last-child {
            margin-bottom: 0px
        }

        .chat .chat-history .message-data {
            margin-bottom: 15px
        }

        .chat .chat-history .message-data img {
            border-radius: 40px;
            width: 40px
        }

        .chat .chat-history .message-data-time {
            color: #434651;
            padding-left: 6px
        }

        .chat .chat-history .message {
            color: #444;
            padding: 10px;
            font-size: 16px;
            border-radius: 7px;
            display: inline-block;
            position: relative;
            max-width: 60%;
        }

        .chat .chat-history .message:after {
            bottom: 100%;
            left: 7%;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
            border-bottom-color: #fff;
            border-width: 10px;
            margin-left: -10px
        }

        .chat .chat-history .my-message {
            background: #efefef
        }

        .chat .chat-history .my-message:after {
            bottom: 100%;
            left: 30px;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
            border-bottom-color: #efefef;
            border-width: 10px;
            margin-left: -10px
        }

        .chat .chat-history .other-message {
            background: #e8f1f3;
            text-align: left;
        }

        .chat .chat-history .other-message:after {
            bottom: 100%;
            left: 20px;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
            border-bottom-color: #e8f1f3;
            border-width: 10px;
            margin-left: -10px;
        }

        .chat .chat-history .adminTxtBg:after {
            border-bottom-color: #0078FF;
            left: auto;
            right: 10px;
        }

        .chat .chat-message {
            padding: 0 20px;
            display: none;
            width: 100%;
            margin-bottom: 20px;
            bottom: 25px;
            position: absolute;
        }

        .online,
        .offline,
        .me {
            margin-right: 2px;
            font-size: 8px;
            vertical-align: middle
        }

        .online {
            color: #86c541
        }

        .offline {
            color: #e47297
        }

        .me {
            color: #1d8ecd
        }

        .float-right {
            float: right
        }

        .clearfix:after {
            visibility: hidden;
            display: block;
            font-size: 0;
            content: " ";
            clear: both;
            height: 0
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;  
            overflow: hidden;
            line-height: 1.2;
        }

        @media only screen and (max-width: 767px) {
            .chat-app .people-list {
                height: 465px;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                left: -400px;
                display: none
            }
            .chat-app .people-list.open {
                left: 0
            }
            .chat-app .chat {
                margin: 0
            }
            .chat-app .chat .chat-header {
                border-radius: 0.55rem 0.55rem 0 0
            }
            .chat-app .chat-history {
                height: 74vh;
                overflow-x: auto
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 992px) {
            .chat-app .chat-list {
                height: 650px;
                overflow-x: auto
            }
            .chat-app .chat-history {
                height: 74vh;
                overflow-x: auto
            }
        }

        @media only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: landscape) and (-webkit-min-device-pixel-ratio: 1) {
            .chat-app .chat-list {
                height: 480px;
                overflow-x: auto
            }
            .chat-app .chat-history {
                height: calc(100vh - 350px);
                overflow-x: auto
            }
        }

        .activeBg{
            background: gainsboro;
            transition: 750ms;
        }
        .bg-color-primary .name{
            font-weight: bold;
            text-transform: capitalize;
        }
        .bg-color-primary .content{
            font-weight: bold; 
        }
        .bg-color-primary .datetime{
            font-size: 12px;
            color: red;
            font-weight: bold
        }
    </style>

    <!-- scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/date-fns/1.30.1/date_fns.min.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-firestore.js"></script>
    <script src="index.js"></script>

    <script>
        // For Firebase JS SDK v7.20.0 and later, measurementId is optional
        const firebaseConfig = {
            apiKey: "{{ env('FIREBASE_API_KEY') }}",
            authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
            databaseURL: "{{ env('FIREBASE_DATABASE_URL') }}",
            projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
            storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET') }}",
            messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}",
            appId: "{{ env('FIREBASE_APP_ID') }}",
            measurementId: "{{ env('FIREBASE_MEASUREMENT_ID') }}"
        };

        /* Firebase configuration for real-time chat application end */
        const defaultProject = firebase.initializeApp(firebaseConfig);
    </script>

    <script type="module">

    const db1 = firebase.firestore();
   
    let loadmore = document.querySelector('.loadmore');
    let loadmore2 = document.querySelector('.loadmore2');
    let unread_loadmore = document.querySelector('.unread_loadmore');
    let limit = 5;
    let text_limit = 6;
    let lastVisibleDocument = null;
    let text_lastVisibleDocument = null;
    let unread_lastVisibleDocument = null;
    let thisURL = '{{ url("/") }}';

     $('.all_user').click(function(){
        $('.all_user_list').css('display', 'block')
        $('.unread_user_list').css('display', 'none')
    })
    $('.unread_user').click(function(){
        $('.unread_user_list').css('display', 'block')
        $('.all_user_list').css('display', 'none')
    })

    $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('body').addClass('side-menu-closed');
        getUserListUpdate()
        unread_count()
        unreadGetUserList()
    })

    loadmore.addEventListener('click', () => {
        getUserListUpdate()
    });

    loadmore2.addEventListener('click', () => {
       loadMoreGetChatList(localStorage.userId)
    });

    unread_loadmore.addEventListener('click', () => {
       unreadGetUserList()
    });

// get data function
async function getUserListUpdate() {
    // var query = await db1.collection("/messagesList/adminId/adminId").orderBy('timestamp', 'desc').where('toId', '==', 'adminId').where('read', '==', false).limit(limit);

    if(lastVisibleDocument == null){
        var query = await db1.collection("/messagesList/adminId/adminId").orderBy('timestamp', 'desc').limit(limit);
    } else{
         var query = await db1.collection("/messagesList/adminId/adminId").orderBy('timestamp', 'desc').startAfter(lastVisibleDocument).limit(limit);
    }

    var userSerial = 0;
    
    query.onSnapshot(snapshot => {
        snapshot.docChanges().forEach((change) => {

            var data = change.doc.data();
            if(change.doc.id != 0){
                if ((change.type === 'modified') || (change.type === 'added')) {  
                    const ul = document.querySelector('.usersList');
                    const un_ul = document.querySelector('.unread_users_List');

                    const oldLi = ul.querySelector('li[data-toId="' + change.doc.id + '"]');
                    const un_oldLi = un_ul.querySelector('li[data-toId="' + change.doc.id + '"]');

                    let user =`<li class="clearfix user user_li ${(data.read == false) && (data.toId == 'adminId') ? 'bg-color-primary': ''} " data-toId="${change.doc.id}" data-toName="${data.fromName}">
                    <img src="${data.image?`/public/${data.image}`:'{{ static_asset("assets/img/female-and-male-icon.jpg") }}'}" alt="avatar">
                    <div class="about">
                            <div class="name">${data.fromName}</div>
                            <div class="content">${(data.content) ? (data.content).substring(0, 20) + "..." : '(Message empty)'}</div>
                            <div class="datetime">${dateFns.distanceInWordsToNow(new Date(parseInt(data.timestamp)))}</div>
                        </div>
                        <div class="status text-danger d-none" id="new_message_${change.doc.id}"> <i class="fa fa-circle online"></i> online </div>
                    </li>`
                    
                    const parser = new DOMParser();
                    const fragment = parser.parseFromString(user, 'text/html');

                    const newLi = fragment.querySelector('li');
                    const un_newLi = fragment.querySelector('li');

                    if (oldLi && oldLi.parentNode === ul) {
                        if (newLi instanceof Node) {
                            ul.replaceChild(newLi, oldLi);
                        } else {
                            // console.log('Could not find the li element with data-id:', data.toId);
                        }
                    } else{
                       if(userSerial > 0){
                           $('.usersList').append(user);
                        }else{
                            $('.usersList').prepend(user);
                       }
                    }

                    if(data.fromId === 'adminId'){
                        $("[data-toid='"+change.doc.id+"']").removeClass('bg-color-primary');
                    } else if(data.fromId !== 'adminId' && data.read == true){
                            $("[data-toid='"+change.doc.id+"']").removeClass('bg-color-primary');
                    } else{
                        $("[data-toid='"+change.doc.id+"']").addClass('bg-color-primary');
                    }

                    userSerial++;
                    
                }
            }

           
            lastVisibleDocument = snapshot.docs[snapshot.docs.length - 1];

        });
        
    });
}
   


// get data function
async function unreadGetUserList() {

    if(unread_lastVisibleDocument == null){
        var query = await db1.collection("/messagesList/adminId/adminId").orderBy('timestamp', 'desc').where('toId', '==', 'adminId').where('read', '==', false).limit(limit);
    } else{
         var query = await db1.collection("/messagesList/adminId/adminId").orderBy('timestamp', 'desc').where('toId', '==', 'adminId').where('read', '==', false).startAfter(unread_lastVisibleDocument).limit(limit);
    }
    var userSerial = 0;
    query.onSnapshot(snapshot => {
        snapshot.docChanges().forEach((change) => {
            var data = change.doc.data();
            if(change.doc.id != 0){
                if ((change.type === 'modified') || (change.type === 'added')) {  

                    const un_ul = document.querySelector('.unread_users_List');
                    const un_oldLi = un_ul.querySelector('li[data-toId="' + change.doc.id + '"]');

                    let user =`<li class="clearfix user user_li ${(data.read == false) && (data.toId == 'adminId') ? 'bg-color-primary': ''} " data-toId="${change.doc.id}" data-toName="${data.fromName}">
                    <img src="${data.image?`/public/${data.image}`:'{{ static_asset("assets/img/female-and-male-icon.jpg") }}'}" alt="avatar">
                    <div class="about">
                            <div class="name">${data.fromName}</div>
                            <div class="content">${(data.content) ? (data.content).substring(0, 20) + "..." : '(Message empty)'}</div>
                            <div class="datetime">${dateFns.distanceInWordsToNow(new Date(parseInt(data.timestamp)))}</div>
                        </div>
                        <div class="status text-danger d-none" id="new_message_${change.doc.id}"> <i class="fa fa-circle online"></i> online </div>
                    </li>`
                    
                    const parser = new DOMParser();
                    const fragment = parser.parseFromString(user, 'text/html');

                    const un_newLi = fragment.querySelector('li');

                    if (un_oldLi && un_oldLi.parentNode === un_ul) {
                        if (un_newLi instanceof Node) {
                            un_ul.replaceChild(un_newLi, un_oldLi);
                        } else {
                            // console.log('Could not find the li element with data-id:', data.toId);
                        }
                    } else{
                        if(userSerial > 0 ){
                            $('.unread_users_List').append(user);
                        }else{
                            $('.unread_users_List').prepend(user);
                       }
                    }
                    console.log(change.type)

                    if(data.fromId === 'adminId'){
                        $("[data-toid='"+change.doc.id+"']").removeClass('bg-color-primary');
                    } else if(data.fromId !== 'adminId' && data.read == true){
                            $("[data-toid='"+change.doc.id+"']").removeClass('bg-color-primary');
                    } else{
                        $("[data-toid='"+change.doc.id+"']").addClass('bg-color-primary');
                    }
                    userSerial++;

                }
            }
            unread_lastVisibleDocument = snapshot.docs[snapshot.docs.length - 1];

        });
        
    });
}
    

$(document).on("click",".user",function(){
    let userId = $(this).attr("data-toId");
    let userName = $(this).attr("data-toName");
    localStorage.setItem('userId', userId)
    localStorage.setItem('userName', userName)
    $(".user").removeClass("activeBg");
    $(this).addClass("activeBg");
    $('.chat-message').css("display", 'block');
    $('.chat-header').css("display", 'block');
    $('.user_name_top').text(userName);
    $(".push_chat").html('');
    $("#others").html('');
    updateUserList(userId);
    var chatResult = getConversaction(userId);
    var userInfo = getUserInfo(userId);
    // console.log('yooo', userInfo);
});
   
function updateUserList(userId){
    // message read query
    db1.collection("/messagesList/adminId/adminId").doc(userId)
    .get().then((querySnapshot) => {
        if(querySnapshot.data().toId === 'adminId'){
            db1.collection("/messagesList/adminId/adminId").doc(userId).update({ read: true })
        }
    })
}
    

  
async function getConversaction(userId) {
    let query = await db1.collection(`/messages/adminId-${userId}/adminId-${userId}`).orderBy('timestamp', 'asc').limitToLast(text_limit);

    query.onSnapshot(snapshot => {
        snapshot.docChanges().forEach((change) => {
            let item = change.doc.data();

            if(change.type === 'added'){
                if((item.fromId == 'adminId' && item.toId == userId) || (item.fromId == userId && item.toId == 'adminId')){
                    let conversaction = '';
                    
                    let dataMessage;
                    async function fetchData() {
                        try {
                            let msg = await generateMessage(item.content);
                            dataMessage = msg;
                            if(item.fromId === 'adminId'){
                                conversaction = `
                                <li class="clearfix" data-same_time="${item.timestamp}">
                                    <div class="message-data text-right">
                                        <span class="message-data-time"> ${dateFns.format(new Date(parseInt(item.timestamp)), "DD MMMM YYYY, h:mm a")} </span>
                                        <img src="{{ static_asset("assets/img/support-person.png") }}" class="border" alt="avatar">
                                    </div>
                                    <div class="message other-message float-right adminTxtBg">${dataMessage}</div>
                                </li>`;
                            }else{
                                conversaction = `
                                <li class="clearfix" data-same_time="${item.timestamp}">
                                    <div class="message-data">
                                        <img src="{{ static_asset("assets/img/female-and-male-icon.jpg") }}" class="border" alt="avatar">
                                        <span class="message-data-time"> ${dateFns.format(new Date(parseInt(item.timestamp)), "DD MMMM YYYY, h:mm a")} </span>
                                    </div>
                                    <div class="message other-message">${dataMessage}</div>
                                </li>`;
                            }
                            if($("[data-same_time='"+item.timestamp+"']").length>0){
                                $("[data-same_time='"+item.timestamp+"']").remove();
                            }
                            await $(".push_chat").append(conversaction);
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    }
                    fetchData();
                }
            }
            if(text_lastVisibleDocument == null){
                text_lastVisibleDocument = change.doc;
            }
        });
    }); 
}


// load more user conversation data
function loadMoreGetChatList(userId){

    let moreChatQuery = db1.collection(`/messages/adminId-${userId}/adminId-${userId}`).orderBy('timestamp', 'desc').startAfter(text_lastVisibleDocument).limit(text_limit);

    moreChatQuery.get().then((snapshot) => {
        snapshot.forEach((doc) => {
            const item = doc.data();
            let conversaction = `
                <li class="clearfix">
                    <div class="message-data ${item.fromId === 'adminId'?'text-right':''} ">
                        <span class="message-data-time"> ${dateFns.format(new Date(parseInt(item.timestamp)), "DD MMMM YYYY, h:mm a")} </span>
                        <img src="${item.image ? item.image : item.fromId === 'adminId' ? '{{ static_asset("assets/img/support-person.png")}}' : '{{ static_asset("assets/img/female-and-male-icon.jpg") }}'}" alt="avatar">
                    </div>
                    <div class="message other-message ${item.fromId === 'adminId'?'float-right adminTxtBg':''}">${item.content} </div>
                </li>`;
            $(".push_chat").prepend(conversaction);
            text_lastVisibleDocument = snapshot.docs[snapshot.docs.length - 1];
        });

    }).catch((error) => {
        // console.log('Error retrieving data:', error);
    });
}


// chat sms add 
let sendSmsField = document.querySelector('.sendSmsValue');
let sendSmsForm = document.querySelector('#sendSmsForm');

sendSmsForm.addEventListener('submit', e => {
    e.preventDefault();

    const message = sendSmsForm.message.value.trim();
    addChat(message)
        .then(() => {
            sendSmsForm.reset()
        })
            .catch(err => console.log(err));
})

async function addChat(message) {
    const now = Date.now().toString();    
    const chat = {
        block: false,
        content: message,
        fromId: "adminId",
        fromMobile: "adminWeb",
        fromName: "Admin",
        image: "",
        read: false,
        timestamp: now,
        toId: localStorage.userId,
        toMobile: "userMobile",
        toName: localStorage.userName,
    };
    const response = await db1.collection('messages').doc(`adminId-${localStorage.userId}`).collection(`adminId-${localStorage.userId}`).doc(now).set(chat);

    // user list message update show
    const update_response = await db1.collection("/messagesList/adminId/adminId").doc(localStorage.userId).update({
        read: false,
        content: message,
        fromId: 'adminId',
        toId: localStorage.userId,
        timestamp: now,
    });

}

// search by name and phone number function
// const searchInput = document.querySelector("#search-input");
// searchInput.addEventListener("keyup", (event) => {
//     console.log('okay')
//     const query = event.target.value.toLowerCase();
//     const collectionRef = db.collection("users");
//     const filteredQuery = collectionRef.where("name", ">=", query)
//         .where("name", "<=", query + "\uf8ff")
//         .orderBy("name")
//         .limit(10);
//     const filteredQueryByPhone = collectionRef.where("phone", ">=", query)
//         .where("phone", "<=", query + "\uf8ff")
//         .orderBy("phone")
//         .limit(10);
//     Promise.all([filteredQuery.get(), filteredQueryByPhone.get()])
//         .then((querySnapshots) => {
//             let results = [];
//             querySnapshots.forEach((querySnapshot) => {
//                 querySnapshot.forEach((doc) => {
//                     const data = doc.data();
//                     if (!results.some((result) => result.id === doc.id)) {
//                         results.push(data);
//                     }
//                 });
//             });
//             // Handle search results here
//         })
//         .catch((error) => {
//             console.log("Error getting documents:", error);
//         });
// });

let chat_history = document.querySelector('.chat-history')

chat_history.addEventListener('scroll', function(){
    if(parseInt(chat_history.scrollHeight + chat_history.scrollTop) === chat_history.clientHeight){
        $(".loadmore2").removeClass("d-none-transion");
    } else{
        $(".loadmore2").addClass("d-none-transion");
    }
})


// unread real time count function 
function unread_count()
{
    db1.collection("/messagesList/adminId/adminId").where("toId", "==", 'adminId')
            .where("read", "==", false)
            .onSnapshot(function(snapshot) {
                var numUnreadDocuments = snapshot.size;
                $('.unread_count').text(numUnreadDocuments);
            });
}


// Validate If Message Has A Valid URL
const urlPattern = new RegExp('(?:https?):\/\/(\w+:?\w*)?(\S+)(:\d+)?(\/|\/([\w#!:.?+=&%!\-\/]))?');
const isValidUrl = urlString => {
    var urlPattern = new RegExp('^(https?:\\/\\/)?'+ // validate protocol
    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // validate domain name
    '((\\d{1,3}\\.){3}\\d{1,3}))'+ // validate OR ip (v4) address
    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // validate port and path
    '(\\?[;&a-z\\d%_.~+=-]*)?'+ // validate query string
    '(\\#[-a-z\\d_]*)?$','i'); // validate fragment locator
    return !!urlPattern.test(urlString);
}

async function whatIsThis(urlString){
    return new Promise(function(resolve, reject) {
        if(urlString.indexOf('ChatImageMedia') !== -1){
            resolve(`image`);
        }else if(isValidUrl(urlString)){
            resolve(`link`);
        }else{
            resolve('text')
        }
    });
}

 function parseLinkMessage(urlString){
    const url = new URL('', urlString);
    var makeURL = url.pathname.replace('.html', '');
    var thisHostname = '{{ request()->getHost() }}';
    // console.log(thisHostname, url.hostname);
    if(url.hostname === thisHostname){
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: `${thisURL}/api/v2/products${makeURL}`,
                success: function(data) {
                    let response = data.data[0];
                    resolve(`<a href="${response.link}" target="_blank" title="${response.name}"><div class="card text-sm mb-3" style="max-width: 540px;">
                                <div class="row no-gutters p-2">
                                    <div class="col-md-4">
                                        <img src="${thisURL}/public/${response.thumbnail_image}" class="card-img" alt="...">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body p-0 pl-1">
                                            <p class="line-clamp-2 m-0 p-0"><small>${response.name}</small></p>
                                            <span class="m-0 text-primary"><small>${response.main_price}</small></span>
                                            <span class="m-0 text-secondary"><small><s>${response.stroked_price}</s></small></span>
                                        </div>
                                    </div>
                                </div>
                            </div></a>`) // Resolve promise and go to then()
                },
                error: function(err) {
                    resolve(`<a href="${urlString}">${urlString}</a>`) // Reject the promise and go to catch()
                }
            });
        });
    }else{
        return new Promise(function(resolve, reject) {
            resolve(`<a href="${urlString}">${urlString}</a>`);
        });
    }
 }

async function finalMessage(messageType, messageContent){
    return new Promise(function(resolve, reject) {
        if(messageType === 'image'){
            resolve(`<img src="${messageContent}" alt="Image" width="200" />`);
        }else if(messageType === 'text'){
            resolve(messageContent != '' ? messageContent : 'empty message');
        }else if(messageType === 'link'){
            try {
                let data = parseLinkMessage(messageContent)
                .then((data) => { 
                    resolve(data);
                });
            } catch (error) {
                resolve(`<a href="${messageContent}">${messageContent}</a>`);
            }
        }
    });
}

async function generateMessage(messageContent){
    try {
        const messageType = await whatIsThis(messageContent);
        const response = await finalMessage(messageType, messageContent);
        return response;
    } catch (error) {
        console.log(error)
    }
}

function getLastPart(url) {
  const parts = url.split('/');
  return parts.at(-1);
}

function formatDatetime(datetimeString) {
  const date = new Date(datetimeString);

  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');

  return `${day}-${month}-${year} ${hours}:${minutes}`;
}

function getUserInfo(userID){
    // let ids = [userID];
    return new Promise(function(resolve, reject) {
        $.ajax({
            type: "POST",
            url: `{{ url('/') }}/get-user-by-id`,
            data: { id: userID},
            success: function(data) {
                let response = data;
                resolve(response) // Resolve promise and go to then()
                if(!data.result){
                    AIZ.plugins.notify('warning', data.message);
                }
                $('#others').html(data.view);
            },
            error: function(err) {
                resolve(err) // Reject the promise and go to catch()
                AIZ.plugins.notify('warning', err.message);
                $('#others').html(err.view);
            }
        });
    });
}




</script>
@endsection
