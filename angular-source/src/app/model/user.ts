export class User {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	username: string = null;

	name: string = null;

	surname: string = null;

	email: string = null;

	thumbnail: string = null;

	token: string = null;

}