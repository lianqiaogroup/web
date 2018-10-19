/*
 * 地址模块的相关接口
 * 包含：
 * 1. 列表
 * 2. 单个查询
 * 3. 设置默认
 * 4. 新增/保存 
 */

import Service from './Service';

const checkLogin = (request) => {
	return Service({
		url: '/logInfo.php',
		method: 'GET',
	})
}


/**
 * @description             获取所有地址数据
 * @auth 					需要的登陆状态
 */
const list = (request) => {
	return Service({
		url: '/address/list',
		method: 'GET',
		auth: true,
		mock: true,
	});
}



/**
 * @description             获取单个地址的详细信息
 * @auth 					需要的登陆状态
 */
const read = (request) => {
	return Service({
		url: '/address/',
		method: 'GET',
		auth: true,
	});
}



/**
 * @description             获取地区数据
 */
const getRegion = (request) => {
	return Service({
		url: '/address/region',
		method: 'GET',
		mock: false,
	});
}


const checkAddress = (code) => {
	return Service({
		url: '/address/region/par?code=' + code,
		method: 'GET',
		mock: false,
	});
}


export default {
	checkLogin,
	list,
	read,
	getRegion,
    checkAddress,
}