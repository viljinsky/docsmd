var DEBUG = false; 
/** Путь к файлу ./libs/docsmd/proc.php */
var PROC_MODULE = './libs/docsmd/proc.php';

/**
 * Управление сообщениями
 * @param {type} comments - контейнер сообщений
 * @param {type} options
 * 
 *     user_id
 *     role_id
 *     
 * @returns {DocManager}
 */
function DocManager(comments,options){
    
    var comments_inner;
    
    var user_id  = options['user_id'];
    var role_id  = options['role_id'];
    
    var page = 'index';
    var serch = location.search;
    if (serch!==''){
        var a = serch.slice(1).split('&');
        for (i in a){
            var p = a[i].split('=');
            if (p[0]==='page'){
                page = p[1];
                break;
            }
        }
    }
    
    
    var self = this;
    var form = null;

    /**
     * содержимое формы ввода сообщений
     * @type String
     */
    var formText = '<div class="dialog-form-title">Ваше сообщение:</div>'+
        '<div class="dialog-form-content">'+    
        '<textarea rows="10" cols="100" name="message" class="user-message" required>Ответ на вопрос</textarea><br>'+
        '<div class="dialog-form-footer">'+
        '<input type="submit" value="Отправить">'+
        '<input type="reset" value="Отмена">'+
        '</div>'+
        '</div>';

    /**
     * Форма редактирования сообщений
     * @param {type} parent
     * @param {type} callback
     * @param {type} message
     * @returns {DocManager.form|Element}
     */    
    function editForm(parent,callback,message){
        form = document.createElement('form');
        form.innerHTML=formText;
        form.className='dialog-form';
        if (typeof message !=='undefined'){
            form.message.innerHTML = message;
        }
        
        form.onsubmit = function(){
            var data = new FormData(this);
            data.append('user_id',user_id);
            data.append('topic_name',page);
            callback(data);
            parent.removeChild(form);
            form=null;
            return false;
        };
        
        form.onreset = function(){
            parent.removeChild(form);
            form=null;
            return false;
        };
        
        parent.appendChild(form);
        
        return form;
    }
    
    /**
     * Позиционирование окна на указанное сообщение
     * @param {type} item_id
     * @returns {undefined}
     */
    this.goup =function(element){
        var item_id = element.getAttribute('data-replay-to');
        
        for(var i=0;i<comments.children.length;i++){
            var el = comments.children[i];
            if (el.hasAttribute('data-id') && el.getAttribute('data-id')==item_id){
                if (el!==null){
                    var r = el.getBoundingClientRect();
                    window.scrollBy(0,r.top);
                }
                break;
            }
        }
    };
    


    /**
     * Форма для добавления аттача
     * @param {type} element
     * @param {type} item_id
     * @param {type} callback
     * @returns {undefined}
     */
    function uploadForm(element,callback){
        var form = document.createElement('form');
        form.className='dialog-form';
        form.innerHTML= '<div class="dialog-form-title">Загрузить файл</div>'
                       +'<div class="dialog-form-content">'
                       +'   <input type="file" name="screenshort" required>'
                       +'</div>'
                       +'<div class="dialog-form-footer">'
                       +'<input type="submit" value="Загрузить">'
                       +'<input type="reset" value="Отмена">'
                       +'</div>';
        document.body.appendChild(form);
        form_center(form);
        
        form.onsubmit = function(){
            callback(new FormData(this));
            document.body.removeChild(form);
            form = null;
            return false;
        };
        
        form.onreset = function(){
            document.body.removeChild(form);
            form=null;
            return false;
        };
        
    }

    
    /**
     * Получить заголовок сообщения пользователя
     * @param {type} item_id
     * @param {type} callback
     * @returns {undefined}
     */
    this.get_comment_header = function (item_id,callback){
        var request = Request(function(text){
            console.log(text);
            var a = JSON.parse(text);
            if (a['error']===0){
                callback(a['header']);
            } else {
                alert()
            }
        });
        request.open('POST',PROC_MODULE);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=comment-header&item_id='+item_id);
        
    }

    this.update_message_count=function(){
        var docpage = document.querySelector('.docpage');
        var topic = docpage.getAttribute('data-page');
        var request = Request(function(text){
            var header = comments.querySelector('.comments-header');
            header.innerHTML=text;
        });
        request.open('POST',PROC_MODULE);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send("command=count&topic="+topic);
    }
    
    
    
    /**
     * Новое сообщение пользователя
     * @returns {undefined}
     */
    this.append_comment=function(){

        if (user_id===0){
            alert('Необходимо войти');
            return;
        }

        form = editForm(document.body,function(data){
            
            var request = Request(function(text){
                
                var d = document.createElement('div');
                d.className='comment';
                d.innerHTML=text;
                var ff = comments_inner.appendChild(d);
                self.add_comments_button(ff);
                self.update_message_count();
                
            });

            data.append('command','add');
            request.open('POST',PROC_MODULE );//php_path+'proc.php');
            request.send(data);
            
        });
        form_center(form);
    };
    
    /**
     * Пользователь хочет редактировать своё сообщение
     * @param {type} comment_item
     * @returns {undefined}
     */
    this.edit_comment=function(comment){
        
        var comment_id = comment.children[0].getAttribute('data-comment-id');
        
        var request = Request(function(text){
            
            form = editForm(document.body,function(data){
                var r = Request(function(text){
                    comment.innerHTML = text;
                    self.add_comments_button(comment);
                });
                
                data.append('command','edit');
                data.append('item_id',comment_id);
                r.open('POST',PROC_MODULE);//'edit_message.php');
                r.send(data);
                
            });
            
            form.message.innerHTML=text.replace(/<br>/g,"\n").replace(/\r/g,""); 
            document.body.appendChild(form);
            form_center(form);
        });
        
        request.open('POST',PROC_MODULE);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=quotes&item_id='+comment_id);
        
    };
    
    /**
     * Пользователь хочет ответить на сообщение другого пользователя
     * @param {type} comment_item
     * @returns {undefined}
     */
    this.replay_comment=function(comment){        
        var comment_id = comment.children[0].getAttribute('data-comment-id');
        self.get_comment_header(comment_id,function(header){
        
            form = editForm(document.body,function(data){

                var request = Request(function(text){
                    var d = document.createElement('div');
                    d.className='comment';
                    d.innerHTML=text;
                    comments_inner.insertBefore(d,comment.nextElementSibling);
                    self.add_comments_button(d);
                    self.update_message_count();
                });

                data.append('command','replay');
                data.append('replay_to',comment_id);
                request.open('POST',PROC_MODULE);
                request.send(data);

            },header);
            
            form_center(form);
        });
    };
    
    /**
     * Пользователь хочет удалить своё сообщение
     * @param {type} comment_item
     * @returns {undefined}
     */
    this.remove_comment=function(comment){
        
        var comment_id = comment.children[0].getAttribute('data-comment-id');
        
        if (confirm('Удалить сообщение '+comment_id)){
        
            var request= Request(function(text){
                console.log(text);
                var a = JSON.parse(text);
                if (a['error']===0){
                    comments_inner.removeChild(comment);
                    self.update_message_count();
                } else {
                    alert(a['message']);
                }
            });
            
            request.open('POST',PROC_MODULE);
            request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            request.send('command=delete&item_id='+comment_id);
        }
    };

    /**
     * Перезагрузка сообщения
     * @param {type} comment_item
     * @returns {undefined}
     */
    this.reload_item=function(comment){
        var item_id=comment.children[0].getAttribute('data-comment-id');
        var request = Request(function(text){
            comment.innerHTML =text;
//            self.add_mark_buttons(comment);
            self.add_comments_button(comment);
//            self.add_attach_butons(comment);            
        });
        request.open('POST',PROC_MODULE);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=reload-item&item_id='+item_id);
        
    };
    
    /**
     * Пользователь добавляет аттачмент к своему сообщению
     * @param {type} comment_item
     * @returns {undefined}
     */
    this.add_attachment=function(comment){
        var item_id=comment.children[0].getAttribute('data-comment-id');
        uploadForm(comment,function(data){
            var request = Request(function(text){
                console.log("'"+text+"'");
                var a=JSON.parse(text);
                
                if (a['error']===0){
                    self.reload_item(comment);
//                    self.add_attach_butons(comment);
                } else {
                    alert(text);
                }    
                
            });
            request.open('POST',PROC_MODULE);
            data.append('command','upload-attach');
            data.append('item_id',item_id);
            request.send(data);
        });
        return false;
    };
    
    /**
     * Пользователь удаляет аттачмент своего сообщения
     * @param {type} comment_item
     * @returns {undefined}
     */
    this.delete_attachment=function(comment,image_id){
        if (confirm('Удалить прикреплённый файл')){
            var request = Request(function(text){
                console.log(text);
                self.reload_item(comment);
            });
            request.open('POST',PROC_MODULE);
            request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            request.send('command=delete-attachment&image_id='+image_id);
        }
    };
    
    this.user_messages=function(user_id){
        var request = new Request(function(text){
            pagecontent.innerHTML=text;
//            alert(text);
        });
        request.open('POST',PROC_MODULE);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=user-messages&user_id='+user_id);
    };
    
    /**
     * Обработсчик кнопок сообщения
     *   добавить<br>
     *   изменить<br>
     *   ответить<br>
     *   удалить<br>
     *   
     *   прикрепить файл<br>
     *   удалить файл<br>
     *     
     * @param {type} event
     * @returns {Boolean}
     */
    this.comments_button_click=function(event){
        
        var target = event.target;
        var action;
        var p = target.closest('.comment-item');
//        var user_id = +p.getAttribute('data-user-id');
        
        console.log(target.tagName);
        
        // только для администратора !!!
        if (target.tagName==='IMG' && target.hasAttribute('data-user-id')){
            auth.user_permission(target.getAttribute('data-user-id'));
            return false;
        }
        if (target.tagName==='A' && target.hasAttribute('data-action')){
            action =target.getAttribute('data-action');
            self[action](user_id);
//            alert(target.getAttribute('data-action'));
            return false;
        }
        
        
        
        if (target.hasAttribute('data-action')){
        
            if (user_id<=0){
                alert('Необходимо войти');
                return false;
            }

            if (form===null){
//                var target = event.target;
                action = target.getAttribute('data-action');
                console.log(' action ->'+action);
                if (action==='delete_attachment'){
                    $image_id = target.closest('.attach').getAttribute("data-attach-id");
                    self.delete_attachment(this,$image_id);
                } else {
                    self[action](this);
                }
            }
        }
    };
    
    
    /**
     * Добавление кнопок редактирования аттача
     * @param {type} comments_item
     * @returns {undefined}
     */
    this.add_attach_butons=function(comment){
        var t = comment.querySelector('.attachments');
//        alert(t);
        if (t!==null && user_id!==0){
            
            if (+comment.children[0].getAttribute('data-user-id')===user_id) {

                for (var i=0;i<t.rows.length;i++){
                    var c = t.rows[i].insertCell(t.rows[i].cells.length);
                    c.innerHTML='<button>Удалить</button>';
                }
            }
                t.onclick = function(event){
                    if (event.target.tagName==='BUTTON'){
                        var c = event.target.closest('tr');
                        self.delete_attachment(comment,c.getAttribute('data-attach-id'));
                        return false;
                    } else if (event.target.tagName==='A'){
                        show_image(event.target.href);
                        return false;
                    }
                };
            
        }        
    };
    
    
    this.mark=function(comment,item_id,value){
        var request = Request(function(text){
            try{
                var result = JSON.parse(text);
                var buttons = comment.querySelector(".mark-buttons");
                buttons.querySelector('.mark-up').innerHTML = "+("+result["up"]+")";
                buttons.querySelector('.mark-down').innerHTML = "-("+result["down"]+")";
            } catch(err){
                alert(err+' '+text);
            }
        });
        request.open('POST',PROC_MODULE);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=mark&item_id='+item_id+'&user_id='+user_id+"&mark="+value);
        
    }
    
    this.mark_up=function(comment,item_id){
        self.mark(comment,item_id,'true');
    }
    
    this.mark_down = function(comment,item_id){
        self.mark(comment,item_id,'false');
    }
    
    this.add_mark_buttons=function(comment){
        
        var dataMark =  comment.children[0].getAttribute('data-mark');
        var data_user_id = +comment.children[0].getAttribute('data-user-id');
        
        var a = dataMark.split(',');
        console.log(dataMark);
        var mark_buttons = document.createElement('div');
        mark_buttons.className = 'mark-buttons';
        mark_buttons.style.cssText = "position:absolute;top:0px;right:0px;";
        
        if (user_id!==0 && user_id!==data_user_id){
            // добовление кнопок отметки
            
            
            var btn = document.createElement('button');
            btn.className="mark-up";
            btn.setAttribute('data-action','mark_up');
            btn.innerHTML='+('+a[0]+')';
            btn.title='Сообщение было полезно';
            mark_buttons.appendChild(btn);
            
            btn = document.createElement('button');
            btn.className="mark-down";
            btn.setAttribute('data-action','mark_down');
            btn.innerHTML='-('+a[1]+')';
            btn.title='Сообщение не имеет смысла';
            mark_buttons.appendChild(btn);
            
            mark_buttons.onclick= function(event){
              var target =event.target;
              var action = target.getAttribute('data-action');
              var p = this.closest('.comment-item');
              var item_id = p.getAttribute('data-comment-id');
              self[action](comment,item_id);
              event.stopPropagation();
              return false;
            };
        } else {
            mark_buttons.innerHTML=dataMark;
        }
        comment.children[0].appendChild(mark_buttons);
        
    };
    
    
    
    
    var imForm = null;
    
    function imageForm(){
        var form = document.createElement('div');
        form.style.cssText = "position:fixed;left:10px; top:10px; padding:10px; background:#fff; border:1px solid #ccc;";
        form.innerHTML='<div><button title="Закрыть">X</button></div><div class="content" style="width:400px;"></div>';
        document.body.appendChild(form);
        
        form.onclick=function(){
            document.body.removeChild(form);
            imForm=null;
        };
        
        this.setImage=function(link){
            var content = form.querySelector(".content");
            content.innerHTML='<img width="400px" src="'+link+'" alt="">';
        };
        
    }
    
    
    function show_image(link){
        if (imForm===null){
            imForm = new imageForm();// document.createElement('div');
        }   
        imForm.setImage(link);
    }
    
    
    
    function createCommentButton(command){
        var command_text = {
            'add_attachment':'Прикрепить файл',
            'edit_comment'  :'Изменить',
            'remove_comment':'Удалить',
            'replay_comment':'Ответить'
        };
        var button = document.createElement('button');
        button.setAttribute('data-action',command);
        button.innerHTML = command_text[command];
        return button;
    }
    
    /**
     * Добаление кнопок к сообщению
     * @param {type} comments_item
     * @returns {undefined}
     */
    this.add_comments_button=function(comment){
        var permissions = ['add_attachment','edit_comment','replay_comment','remove_comment'];
        
        var buttons = document.createElement('div');
        buttons.className = 'comment-buttons';
        
        var p  = comment.children[0].getAttribute('data-permission').split(',');
        for (var i=0;i<permissions.length;i++){
            if (Boolean(p[i])){
                buttons.appendChild(createCommentButton(permissions[i]));
            }
        }
        
        comment.appendChild(buttons);
        comment.onclick=self.comments_button_click;
        
        buttons.style.opacity = '.5';
        
        comment.onmouseenter= function(){
            buttons.style.opacity = '1.0';
        };
        comment.onmouseleave = function(){
            buttons.style.opacity = '0.5';
        };
        
        self.add_attach_butons(comment);        
       
        self.add_mark_buttons(comment);
        
    };
    
    /**
     * Чтение списка сообщений по теме
     * @returns {undefined}
     */
    this.read_comments = function(){
        
        var request = Request(function(response){
            comments.innerHTML = response;
            comments_inner=comments.querySelector('.comments-inner');
            for (var i=0;i<comments_inner.children.length;i++){
                self.add_comments_button(comments_inner.children[i]);
            }
            comments.querySelector('.comments-footer').onclick = self.append_comment;
//            comments_inner.onclick = self.comments_button_click;
        });
        
        request.open('POST',PROC_MODULE);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=read&page='+page);
        
    };
    
    this.read_comments();    

}