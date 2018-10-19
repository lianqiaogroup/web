export default {

	// 更新登录信息
	updateLogInfo(state, params) {
		state.auth.username = params.username;
		state.auth.login_name = params.login_name
		state.auth.is_admin = params.is_admin == "1" ? true : false;
		state.auth.is_root = params.is_root == "1" ? true : false;
		state.auth.uid = params.uid;
		state.auth.id_department = params.id_department || 0;
	},

}
