<script>
import ajax from './ajax';
import UploadDragger from './upload-dragger.vue';

export default {
  inject: ['uploader'],
  components: {
    UploadDragger
  },
  props: {
    type: String,
    action: {
      type: String,
      required: true
    },
    name: {
      type: String,
      default: 'file'
    },
    data: Object,
    headers: Object,
    withCredentials: Boolean,
    multiple: Boolean,
    accept: String,
    onStart: Function,
    onProgress: Function,
    onSuccess: Function,
    onError: Function,
    beforeUpload: Function,
    drag: Boolean,
    onPreview: {
      type: Function,
      default: function() {}
    },
    onRemove: {
      type: Function,
      default: function() {}
    },
    fileList: Array,
    autoUpload: Boolean,
    listType: String,
    httpRequest: {
      type: Function,
      default: ajax
    },
    disabled: Boolean
  },

  data() {
    return {
      mouseover: false,
      reqs: {}
    };
  },

  methods: {
    isImage(str) {
      return str.indexOf('image') !== -1;
    },
    handleChange(ev) {
      const files = ev.target.files;

      if (!files) return;
      this.uploadFiles(files);
      this.$refs.input.value = null;
    },
    uploadFiles(files) {
      let postFiles = Array.prototype.slice.call(files);
      if (!this.multiple) { postFiles = postFiles.slice(0, 1); }

      if (postFiles.length === 0) { return; }
      let accept = ['image/bmp', 'image/gif', 'image/jpeg', 'image/jpeg', 'image/png', 'video/mp4','psd'];

      postFiles.forEach(rawFile => {
        this.onStart(rawFile);
        // 是否自动上传
        if (this.autoUpload) {
          // 判断格式
          
            console.log()
          if(accept.includes(rawFile.type) || rawFile.name.indexOf('psd')>-1){
               // 判断大小
            if (rawFile.size<(50*1024*1000)) {
              this.upload(rawFile);
            } else {
              this.onError({'msg': '文件大小不能超过50兆'}, rawFile);
              this.abort(rawFile);
            }
          }else{
                this.onError({'msg': '只能上传jpg 、jpeg 、gif 、png 、bmp、mp4、psd'}, rawFile);
                this.abort(rawFile);
          }
        //   if (!accept.includes(rawFile.type)) {
        //     this.onError({'msg': '只能上传jpg 、jpeg 、gif 、png 、bmp、mp4、psd'}, rawFile);
        //     this.abort(rawFile);
        //   } else {
        //     // 判断大小
        //     if (rawFile.size<(50*1024*1000)) {
        //       this.upload(rawFile);
        //     } else {
        //       this.onError({'msg': '文件大小不能超过50兆'}, rawFile);
        //       this.abort(rawFile);
        //     }
        //   }
        }
      });
    },
    upload(rawFile, file) {
      if (!this.beforeUpload) {
        return this.post(rawFile);
      }

      const before = this.beforeUpload(rawFile);
      if (before && before.then) {
        before.then(processedFile => {
          if (Object.prototype.toString.call(processedFile) === '[object File]') {
            this.post(processedFile);
          } else {
            this.post(rawFile);
          }
        }, () => {
          this.onRemove(rawFile, true);
        });
      } else if (before !== false) {
        this.post(rawFile);
      } else {
        this.onRemove(rawFile, true);
      }
    },
    abort(file) {
      const { reqs } = this;
      if (file) {
        let uid = file;
        if (file.uid) uid = file.uid;
        if (reqs[uid]) {
          reqs[uid].abort();
        }
      } else {
        Object.keys(reqs).forEach((uid) => {
          if (reqs[uid]) reqs[uid].abort();
          delete reqs[uid];
        });
      }
    },
    post(rawFile) {
      const { uid } = rawFile;
      // ======= cullen 2018-07-27
      const key = this.createFileName(rawFile.name, this.data.dir);
      // =======
      let options = {
        headers: this.headers,
        withCredentials: this.withCredentials,
        file: rawFile,
        data: this.data,
        filename: this.name,
        action: this.action,
        onProgress: e => {
          this.onProgress(e, rawFile);
        },
        onSuccess: res => {
          this.onSuccess(res, rawFile, key);
          delete this.reqs[uid];
        },
        onError: err => {
          this.onError(err, rawFile);
          delete this.reqs[uid];
        }
      };
      // ======= cullen 2018-07-27
      options.data.key = key;
      // ======= cullen 2018-07-27
      const req = this.httpRequest(options);
      this.reqs[uid] = req;
      if (req && req.then) {
        req.then(options.onSuccess, options.onError);
      }
    },
    handleClick() {
      if (!this.disabled) {
        this.$refs.input.click();
      }
    },
    createFileName(filename, dir) {
      let timestamp = new Date().getTime();
      let randomNumber = parseInt(Math.random()*10000).toString();
      let exter = filename.toLowerCase().split('.').splice(-1);
      return `${dir}${timestamp}${randomNumber}.${exter}`;
    }
  },

  render(h) {
    let {
      handleClick,
      drag,
      name,
      handleChange,
      multiple,
      accept,
      listType,
      uploadFiles,
      disabled
    } = this;
    const data = {
      class: {
        'el-upload': true
      },
      on: {
        click: handleClick
      }
    };
    data.class[`el-upload--${listType}`] = true;
    return (
      <div {...data}>
        {
          drag
          ? <upload-dragger disabled={disabled} on-file={uploadFiles}>{this.$slots.default}</upload-dragger>
          : this.$slots.default
        }
        <input class="el-upload__input" type="file" ref="input" name={name} on-change={handleChange} multiple={multiple} accept={accept}></input>
      </div>
    );
  }
};
</script>
