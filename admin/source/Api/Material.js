// material

import Service from './Service';

let config = {
	host: '', // 接口地址
	key: '${filename}', // 远端文件名
	dir: '', // 目录
	policy: '',
	OSSAccessKeyId: '',
	signature: '',
	success_action_status: '200',
}

const getList = (data) => {
    return Service({
        url:'/Resource.php?act=query',
        method:'POST',
        data
    })
}

const add = (data) => {
	return Service({
		url: '/material.php?act=add',
        url: '/Resource.php?act=create',
		method: 'POST',
		data
	})
}

const delet = (data) => {
    var formdata = new FormData();
    formdata.append('resource_id',data.id)
    return Service({
        url:'/Resource.php?act=delete',
        method:'POST',
        data: formdata
    })
}

// 获取配置
const getConfig = (_) => {
	return new Promise((resolve, reject) => {
		Service({
			url: '/material.php?act=getconfig',
			method: 'GET',
		}).then(res => {
			if (res.ret==1) {
				config.host = res.data.host;
				config.dir = res.data.dir;
				config.key = res.data.dir + '${filename}';
				config.policy = res.data.policy;
				config.OSSAccessKeyId = res.data.accessid;
				config.signature = res.data.signature;
				resolve(config);
			} else {
				reject({});
			}
		});
	})
}

// 获取分类
const getType = (_) => {
	return Service({
		url: '/material.php?act=type',
		method: 'GET',
		cache: true,
		cacheName: 'material_get_type',
	})
}

// 获取标签
const getTags = (_) => {
	return Service({
        url: '/Resource.php?act=types',
		method: 'GET',
	})
}
//
const getMember  = (data) => {
    return Service({
        url: '/Resource.php?act=authorList',
        method: 'GET'
    })
}

// 上传文件
const upload = (content) => {
	let _key = _createFileName(content.file.name, config.dir);
	let form = new FormData();
		form.append('key', _key);
		form.append('policy', config.policy);
		form.append('OSSAccessKeyId', config.OSSAccessKeyId);
		form.append('signature', config.signature);
		form.append("file", content.file);
		form.append("success_action_status", config.success_action_status);

	return new Promise((resolve, reject) => {
		Service({
			url: config.host,
			method: 'POST',
			data: form,
		}).then( _ => {
			resolve({
				code: 200,
				orign_filename: _key,
				path: `${config.host}/${_key}`
			})
		}).catch(err=>{
			reject(err)
		})
	});
}



/*
	_createFileName
	创建文件名
	example:   '${目录}${文件名}${格式}'
*/
const _createFileName = (filename, dir) => {
	let timestamp = new Date().getTime();
	let randomNumber = parseInt(Math.random()*10000).toString();
	let exter = filename.toLowerCase().split('.').splice(-1);
	return `${dir}${timestamp}${randomNumber}.${exter}`;
}


export default {
    getList,
	add,
	getConfig,
	getType,
	getTags,
    upload,
    getMember,
    delet
}
