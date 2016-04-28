var DEBUG = false; 
/** Путь к файлу ./libs/docsmd/proc.php */
var PROC_MODULE = './libs/docsmd/proc.php';

//function form_center(form){
//
//    var w  = document.documentElement.clientWidth,
//        h  = document.documentElement.clientHeight,
//        w1 = form.clientWidth,
//        h1 = form.clientHeight;
//
//    form.style.left= Math.floor((w-w1)/2)+'px';
//    form.style.top=Math.floor((h-h1)/2)+'px';
//}
    


/**
 * На документе должна быть форма класса auth
 * @param {type} comments
 * @param {type} options
 * 
 *     user_id
 *     role_id
 *     
 * @returns {DocManager}
 */
function DocManager(comments,options){
    
    var comments_inner = comments.querySelector('.comments-inner');
    
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
                       +'<div class="dialog-form-content"><input type="file" name="screenshort" required></div>'
                       +'<div class="dialog-form-footer">'
                       +'<input type="submit" value="Загрузить">'
                       +'<input type="reset" value="Отмена">'
                       +'</div>';
        element.appendChild(form);
        form_center(form);
        
        form.onsubmit = function(){
            callback(new FormData(this));
            element.removeChild(form);
            form = null;
            return false;
        };
        
        form.onreset = function(){
            element.removeChild(form);
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
    
    
    
    this.add_comment=function(){
        var comment_item = document.createElement('div');

        var message = document.createElement('div');
        message.className = 'comment-item';
        message.innerHTML='Новое сообщение';

        comment_item.className='comments-container';
        comment_item.setAttribute('data-comment-id','-1');

        comment_item.appendChild(message);
        comments_inner.appendChild(comment_item);
        self.add_comments_button(comment_item);
        comments_inner.appendChild(comment_item);
        return comment_item;
    };
    
    /**
     * Новое сообщение пользователя
     * @returns {undefined}
     */
    this.append_comment=function(){

        if (user_id<0){
            alert('Необходимо войти');
            return;
        }

        form = editForm(document.body,function(data){
            
            var request = Request(function(text){
                
                var d = document.createElement('div');
//                d.style.position='relative';
                d.className='comment';
                d.innerHTML=text;
                var ff = comments_inner.appendChild(d);
                self.add_comments_button(ff);
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
    this.edit_comment=function(comment_item){
        
        var comment_id = comment_item.children[0].getAttribute('data-comment-id');
        
        var request = Request(function(text){
            
            form = editForm(document.body,function(data){
                var r = Request(function(text){
                    comment_item.innerHTML = text;
                    self.add_comments_button(comment_item);
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
    this.replay_comment=function(comment_item){        
        var comment_id = comment_item.children[0].getAttribute('data-comment-id');
        self.get_comment_header(comment_id,function(header){
        
            form = editForm(document.body,function(data){

                var request = Request(function(text){
                    var d = document.createElement('div');
                    d.className='comment';
                    d.innerHTML=text;
                    comments_inner.insertBefore(d,comment_item.nextElementSibling);
                    self.add_comments_button(d);
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
    this.remove_comment=function(comment_item){
        
        var comment_id = comment_item.children[0].getAttribute('data-comment-id');
        
        if (confirm('Удалить сообщение '+comment_id)){
        
            var request= Request(function(text){
                console.log(text);
                var a = JSON.parse(text);
                if (a['error']===0){
                    comments_inner.removeChild(comment_item);
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
    this.reload_item=function(comment_item){
        var item_id=comment_item.children[0].getAttribute('data-comment-id');
        var request = Request(function(text){
            comment_item.innerHTML =text;
            self.add_comments_button(comment_item);
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
    this.add_attachment=function(comment_item){
        var item_id=comment_item.children[0].getAttribute('data-comment-id');
        uploadForm(comment_item,function(data){
            var request = Request(function(text){
                console.log("'"+text+"'");
                var a=JSON.parse(text);
                
                if (a['error']===0){
                    self.reload_item(comment_item);
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
    this.delete_attachment=function(comment_item,image_id){
        if (confirm('Удалить прикреплённый файл')){
            var request = Request(function(text){
                console.log(text);
                self.reload_item(comment_item);
            });
            request.open('POST',PROC_MODULE);
            request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            request.send('command=delete-attachment&image_id='+image_id);
        }
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
        
        console.log(target.tagName);
        
        if (target.tagName==='A' && target.hasAttribute('data-user-id')){
//            alert(target.getAttribute('data-user-id'));
            auth.user_permission(target.getAttribute('data-user-id'));
            return false;
        }
        
        if (target.hasAttribute('data-action')){
        
            if (user_id<=0){
                alert('Необходимо войти');
                return false;
            }

            if (form===null){
//                var target = event.target;
                var action = target.getAttribute('data-action');
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
    
    /**
     * Добавление кнопок редактирования аттача
     * @param {type} comments_item
     * @returns {undefined}
     */
    this.add_attach_butons=function(comments_item){
        var t = comments_item.querySelector('.attachments');
        if (t!==null){
            
            if (user_id!==0){
            
                if (+comments_item.children[0].getAttribute('data-user-id')===user_id) {
            
                    for (i=0;i<t.rows.length;i++){
                        var c = t.rows[i].insertCell(t.rows[i].cells.length);
                        c.innerHTML='<button>Удалить</button>';
                    }
                }
            
            }
            
            t.onclick = function(event){
                if (event.target.tagName==='BUTTON'){
                    var c = event.target.closest('tr');
                    self.delete_attachment(comments_item,c.getAttribute('data-attach-id'));
                    alert(c.getAttribute('data-attach-id'));
                    return false;
                } else if (event.target.tagName='A'){
                    show_image(event.target.href);
//                    alert(event.target.href);
                    return false;
                }
            }
        }
        
    }
    
    /**
     * Добаление кнопок к сообщению
     * @param {type} comments_item
     * @returns {undefined}
     */
    this.add_comments_button=function(comments_item){
        
        var buttons = document.createElement('div');
        buttons.className = 'comment-buttons';
        
        var button;
        var p  = comments_item.children[0].getAttribute('data-permission');
        var permission = p.split(',');

        if (Boolean(permission[0])){
            button = document.createElement('button');
            button.innerHTML='Прикрепить файл';
            button.setAttribute('data-action','add_attachment');
            buttons.appendChild(button);
        }

        if (Boolean(permission[1])){
            button = document.createElement('button');
            button.innerHTML='Изменить';
            button.setAttribute('data-action','edit_comment');
            buttons.appendChild(button);
        }

        if (permission[2]){
            button = document.createElement('button');
            button.innerHTML='Ответить';
            button.setAttribute('data-action','replay_comment');
            buttons.appendChild(button);
        }
        
        if (Boolean(permission[3])){
            button = document.createElement('button');
            button.innerHTML='Удалить';
            button.setAttribute('data-action','remove_comment');
            buttons.appendChild(button);
        }
        
        comments_item.appendChild(buttons);
        comments_item.onclick=self.comments_button_click;
        
        buttons.style.opacity = '.5';
        
        comments_item.onmouseenter= function(){
            buttons.style.opacity = '1.0';
        };
        comments_item.onmouseleave = function(){
            buttons.style.opacity = '0.5';
        }
       
        
    };
    
    /**
     * Чтение списка сообщений по теме
     * @returns {undefined}
     */
    this.read_comments = function(){
        
        var request = Request(function(response){
            
            comments.innerHTML = response;
            comments_inner=comments.querySelector('.comments-inner');
            console.log('comments inner : '+comments_inner);
            if (role_id !== 0){
                
                console.log(' count '+comments_inner.children.length)
                for (i=0;i<comments_inner.children.length;i++){
                    self.add_comments_button(comments_inner.children[i]);
                }
            
                var button=document.createElement('button');
                button.innerHTML='Новое сообщение';
                button.onclick = self.append_comment;
                comments.appendChild(button);
            }
            
            for(var n=0;n<comments_inner.children.length;n++){
                self.add_attach_butons(comments_inner.children[n]);
                console.log('-->'+n);
            }
            
        });
        request.open('POST',PROC_MODULE);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=read&page='+page+'&allow_write=998&allow_replay=true&allow_attach=1');
        
    };
    
    this.read_comments();    

}