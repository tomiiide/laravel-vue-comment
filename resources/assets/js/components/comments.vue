<template>
  <div class="container">
    <h4>Add Comment</h4>
    <form action="" @submit.prevent="edit ? editComment(comment.id) : createComment()">
      <div class="input-group">
        <textarea name="body" v-model="comment.body" ref="textarea"  class="form-control"></textarea>
      </div>
        <div class="input-group">
          <button type="submit" class="btn btn-primary" v-show="!edit">Add Comment</button>
          <button type="submit" class="btn btn-primary" v-show="edit">Edit Comment</button>
        </div>

    </form>

    <h4>Comments</h4>
      <ul class="list-group">
        <li class="list-group-item" v-for="comment in comments">
          {{comment.body}}
          <a class="btn btn-default" v-on:click=" showComment(comment.id)">Edit</a>
          <a class="btn btn-danger" v-on:click=" deleteComment(comment.id)">Delete</a>
        </li>

      </ul>
  </div>
</template>

<script>
  export default{
    data: function(){
      return {
        edit:false,
        comments:[],
        comment: {
          title:'',
          body: '',
          id: '',
        },
      }
    },


created: function(){
    this.fetchComments();
},
  ready: function(){
    this.fetchComments();
  },

  methods: {
    fetchComments: function(){

      this.$http.get("../api/post/"+window.Laravel.post_id+"/comments")
        .then(function (response){
          this.comments = response.data;
      });
    },

    createComment: function(){
      this.$http.post("../api/post/"+window.Laravel.post_id+"/comment", this.comment)
        .then( function (response){
          this.comment.body= '';
          this.fetchComments();
      });
    },

    editComment: function(comment_id){
      this.$http.patch("../api/post/"+window.Laravel.post_id+"/comment/"+comment_id, this.comment)
        .then( function (response){
          this.comment.body= '';
          this.comment.id= '';
          this.fetchComments();
          this.edit = false;
      });
    },

    deleteComment: function(comment_id){
      this.$http.delete("../api/post/"+window.Laravel.post_id+"/comment/"+comment_id)
        .then( function (response){
          this.comment.body= '';
          this.fetchComments();
      });
    },

    showComment: function(comment_id){
      console.log("clicked"+comment_id);
      console.log(this.comments);
      
      for (var i = 0; i < this.comments.length; i++) {
        if (this.comments[i].id == comment_id) {
          this.comment.body = this.comments[i].body;
          this.comment.id = this.comments[i].id;
          this.edit = true;
        }
      }
    }
  }
}
</script>
