
import Service from './Service';

//标签列表
const tagList = (data) => {
    return Service({
        url: '/ResourceTags.php?act=query&p=1&limit=999999',
        method: 'GET'
    })
}

//增加标签
const addTag = (data) =>{
    var formdata = new FormData();
    if (data.tag_name){
        formdata.append('tag_name',data.tag_name);
    }
    return Service({
        url:'/ResourceTags.php?act=create',
        method:'POST',
        data:formdata
    })
}

//编辑标签
const editTag = (data) => {
    var formdata = new FormData();
    formdata.append('tag_name', data.tag_name);
    formdata.append('tag_id', data.tag_id);

    return Service({
        url: '/ResourceTags.php?act=update',
        method: 'POST',
        data:formdata
    })
}
//删除标签
const deleteTag = (data) => {
    var formdata = new FormData();
    formdata.append('tag_id', data.tag_id);
    return Service({
        url: '/ResourceTags.php?act=delete',
        method: 'POST',
        data: formdata
    })
}


export default {
    tagList,
    addTag,
    editTag,
    deleteTag
}
