export class User {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	private _username: string = null;

	private _name: string = null;

	private _surname: string = null;

	private _email: string = null;

	private _thumbnail: string = null;

	private _apiToken: string = null;

	private _cacheToken: string = null;

    get username(): string {
        return this._username;
    }

    set username(value: string) {
        this._username = value;
    }

    get name(): string {
        return this._name;
    }

    set name(value: string) {
        this._name = value;
    }

    get surname(): string {
        return this._surname;
    }

    set surname(value: string) {
        this._surname = value;
    }

    get email(): string {
        return this._email;
    }

    set email(value: string) {
        this._email = value;
    }

    get thumbnail(): string {
        return this._thumbnail;
    }

    set thumbnail(value: string) {
        this._thumbnail = value;
    }

    get apiToken(): string {
        return this._apiToken;
    }

    set apiToken(value: string) {
        this._apiToken = value;
    }

    get cacheToken(): string {
        return this._cacheToken;
    }

    set cacheToken(value: string) {
        this._cacheToken = value;
    }
}
