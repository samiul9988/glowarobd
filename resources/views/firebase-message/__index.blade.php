@extends('backend.layouts.app')

@section('content')

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card chat-app">
                <div id="plist" class="people-list">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" placeholder="Search...">
                    </div>
                    <ul class="list-unstyled chat-list mt-2 mb-0">
                        <li class="clearfix">
                            <img src="https://bootdey.com/img/Content/avatar/avatar1.png" alt="avatar">
                            <div class="about">
                                <div class="name">Vincent Porter</div>
                                <div class="status"> <i class="fa fa-circle offline"></i> left 7 mins ago </div>
                            </div>
                        </li>
                        <li class="clearfix active">
                            <img src="https://bootdey.com/img/Content/avatar/avatar2.png" alt="avatar">
                            <div class="about">
                                <div class="name">Aiden Chavez</div>
                                <div class="status"> <i class="fa fa-circle online"></i> online </div>
                            </div>
                        </li>
                        <li class="clearfix">
                            <img src="https://bootdey.com/img/Content/avatar/avatar3.png" alt="avatar">
                            <div class="about">
                                <div class="name">Mike Thomas</div>
                                <div class="status"> <i class="fa fa-circle online"></i> online </div>
                            </div>
                        </li>
                        <li class="clearfix">
                            <img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="avatar">
                            <div class="about">
                                <div class="name">Christian Kelly</div>
                                <div class="status"> <i class="fa fa-circle offline"></i> left 10 hours ago </div>
                            </div>
                        </li>
                        <li class="clearfix">
                            <img src="https://bootdey.com/img/Content/avatar/avatar8.png" alt="avatar">
                            <div class="about">
                                <div class="name">Monica Ward</div>
                                <div class="status"> <i class="fa fa-circle online"></i> online </div>
                            </div>
                        </li>
                        <li class="clearfix">
                            <img src="https://bootdey.com/img/Content/avatar/avatar3.png" alt="avatar">
                            <div class="about">
                                <div class="name">Dean Henry</div>
                                <div class="status"> <i class="fa fa-circle offline"></i> offline since Oct 28 </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="chat">
                    <div class="chat-header clearfix">
                        <div class="row">
                            <div class="col-lg-6">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#view_info">
                                    <img src="https://bootdey.com/img/Content/avatar/avatar2.png" alt="avatar">
                                </a>
                                <div class="chat-about">
                                    <h6 class="m-b-0">Aiden Chavez</h6>
                                    <small>Last seen: 2 hours ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-history">
                        <ul class="m-b-0">
                            <li class="clearfix">
                                <div class="message-data text-right">
                                    <span class="message-data-time">10:10 AM, Today</span>
                                    <img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="avatar">
                                </div>
                                <div class="message other-message float-right"> Hi Aiden, how are you? How is the project coming along? </div>
                            </li>
                            <li class="clearfix">
                                <div class="message-data">
                                    <span class="message-data-time">10:12 AM, Today</span>
                                </div>
                                <div class="message my-message">Are we meeting today?</div>
                            </li>
                            <li class="clearfix">
                                <div class="message-data">
                                    <span class="message-data-time">10:15 AM, Today</span>
                                </div>
                                <div class="message my-message">Project has been already finished and I have results to show you.</div>
                            </li>
                        </ul>
                    </div>
                    <div class="chat-message clearfix">
                        <div class="input-group mb-0">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="lab la-telegram-plane"></i></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Enter text here...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <h2>Firebase RealTime Chat</h2>

      <div id="chat">
        <!-- messages will display here -->
        <ul id="messages"></ul>

        <!-- form to send message -->
        <form id="message-form">
          <input id="message-input" type="text" />
          <button id="message-btn" type="submit">Send</button>
        </form>
      </div>

      <style>
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
                    width: 280px;
                    position: absolute;
                    left: 0;
                    top: 0;
                    padding: 20px;
                    z-index: 7
                }

                .chat-app .chat {
                    margin-left: 280px;
                    border-left: 1px solid #eaeaea
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
                    width: 45px;
                    border-radius: 50%
                }

                .people-list img {
                    float: left;
                    border-radius: 50%
                }

                .people-list .about {
                    float: left;
                    padding-left: 8px
                }

                .people-list .status {
                    color: #999;
                    font-size: 13px
                }

                .chat .chat-header {
                    padding: 15px 20px;
                    border-bottom: 2px solid #f4f7f6
                }

                .chat .chat-header img {
                    float: left;
                    border-radius: 40px;
                    width: 40px
                }

                .chat .chat-header .chat-about {
                    float: left;
                    padding-left: 10px
                }

                .chat .chat-history {
                    padding: 20px;
                    border-bottom: 2px solid #fff
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
                    padding: 18px 20px;
                    line-height: 26px;
                    font-size: 16px;
                    border-radius: 7px;
                    display: inline-block;
                    position: relative
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
                    text-align: right
                }

                .chat .chat-history .other-message:after {
                    border-bottom-color: #e8f1f3;
                    left: 93%
                }

                .chat .chat-message {
                    padding: 20px
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
                        height: 300px;
                        overflow-x: auto
                    }
                }

                @media only screen and (min-width: 768px) and (max-width: 992px) {
                    .chat-app .chat-list {
                        height: 650px;
                        overflow-x: auto
                    }
                    .chat-app .chat-history {
                        height: 600px;
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
      </style>

      <!-- scripts -->
    {{-- <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-firestore.js"></script>
    <script src="index.js"></script>

    <script>


        /* Firebase configuration for real-time chat application start */
        var firebaseConfig = {
          apiKey: "AIzaSyDraw7GFabSxgZR_q9JrYZuzEh_t2wvgF8",
          authDomain: "glowarobd.firebaseapp.com",
          databaseURL: "https://glowarobd-default-rtdb.firebaseio.com",
          projectId: "glowarobd",
          storageBucket: "glowarobd.appspot.com",
          messagingSenderId: "809029409917",
          appId: "1:809029409917:web:d830e26af889177e3ae514",
          measurementId: "G-0H0W2CHTK3"
        }
        /* Firebase configuration for real-time chat application end */
        const defaultProject = firebase.initializeApp(firebaseConfig);
        // console.log(defaultProject.name);

      const db = firebase.database();
      const firestoreDB = firebase.firestore();
      const fetchChat = firebase.database().ref("messages/");

      // var allMessages = firebase.database().ref('messages');
      fetchChat.on('value', function(snapshot) {
        // console.log(snapshot);
          snapshot.forEach(function(childSnapshot) {
            var childData = childSnapshot.val();
            // console.table(childData)
          });
      }); --}}




      <?php /*
    // </script>

    // <script type="module">


    //   const db1 = firebase.firestore();

    // const getMarker = async() => {
    //     const snapshot = await firebase.firestore().collection('userList').limit(25)//. get()
    //     snapshot.docs.map((doc) =>{
    //         // console.log(doc);
    //         console.log(doc.data().length);
    //     });
    // }
    // getMarker();

    //   functon getMarker() {
            // const snapshot = db1.collection('userList').get();
            // console.log(snapshot.docs)
            // snapshot.docs.map((doc) =>{
            //     console.log(doc.data())
            // });
        // }

        // getMarker();

    //   let allUser = db1.collection("userList").doc("SF");//collection(db, userList);
    //   allUser.get().then((doc) => {
    //     if (doc.exists) {
    //         console.log("Document data:", doc.data());
    //     } else {
    //         // doc.data() will be undefined in this case
    //         console.log("No such document!");
    //     }
    //     }).catch((error) => {
    //         console.log("Error getting document:", error);
    //     });

    //   db1.collection("userList")
    //   .onSnapshot((snapshopDoc) => {
    //     //   console.log(snapshopDoc);
    //   });
 */
 ?>
 {{--
   /*  db1.collection("cities").get().then((userList) => {
        querySnapshot.forEach((doc) => {
            // doc.data() is never undefined for query doc snapshots
            console.log(doc.id, " => ", doc.data());
        });
    }); */

    //   const getMarker = async() => {
    //       const snapshot = await db1.collection("messages").doc("adminId-2985").collection("adminId-2985").get()
    //       return snapshot.docs.map(doc => doc.data());
    //   }

    //   console.log(getMarker());
    // </script>
 --}}



<script type="module">
    // Import the functions you need from the SDKs you need
    import { initializeApp } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-app.js";
    import { getAnalytics } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-analytics.js";
    // import { firebase } from "https://www.gstatic.com/firebasejs/8.10.1/firebase-firestore.js"
    // TODO: Add SDKs for Firebase products that you want to use
    // https://firebase.google.com/docs/web/setup#available-libraries

    // Your web app's Firebase configuration
    // For Firebase JS SDK v7.20.0 and later, measurementId is optional
    const firebaseConfig = {
      apiKey: "AIzaSyBi8nkr0BQ6EG8ZwqzMpd9lvJxdmjIvayM",
      authDomain: "chat-app-917ef.firebaseapp.com",
      projectId: "chat-app-917ef",
      storageBucket: "chat-app-917ef.appspot.com",
      messagingSenderId: "471258498122",
      appId: "1:471258498122:web:ea2accf8b007ec0f4e12b3",
      measurementId: "G-4XZ07S0MMM"
    };

    // Initialize Firebase
    const app = initializeApp(firebaseConfig);
    const analytics = getAnalytics(app);


    class City {
        constructor (name, state, country ) {
            this.name = name;
            this.state = state;
            this.country = country;
        }
        toString() {
            return this.name + ', ' + this.state + ', ' + this.country;
        }
    }


    // Firestore data converter
    var cityConverter = {
        toFirestore: function(city) {
            return {
                name: city.name,
                state: city.state,
                country: city.country
                };
        },
        fromFirestore: function(snapshot, options){
            const data = snapshot.data(options);
            return new City(data.name, data.state, data.country);
        }
    };


  </script>
@endsection
