export class User {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	username: string;

	name: string;

	surname: string;

	email: string;

	thumbnail: string;

}