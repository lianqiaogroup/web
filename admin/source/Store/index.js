import mutations from './mutations'
import * as actions from './actions'


const state = {
	// 权限管理
	auth: {
		// 用户名
		username: '',
		// 登录用户名
		login_name: '',
		// 是否管理员
		is_admin: false,
		// 是否超管
		is_root: false,
		// 用户ID
		uid: 0,
		// 部门ID
		id_department: 0,
	}
}

const getters = {
	demo: state => state.demo,
}

export default new Vuex.Store({
	state,
	actions,
	getters,
	mutations
})
