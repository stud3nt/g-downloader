import { ResponseStatus } from "../enum/response-status";

export class JsonResponse {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	private _status: number = ResponseStatus.Error;

	private _data: any = null;

    get status(): number {
        return this._status;
    }

    set status(value: number) {
        this._status = value;
    }

    get data(): any {
        return this._data;
    }

    set data(value: any) {
        this._data = value;
    }

    public success(): boolean {
		return (this._status === ResponseStatus.Success);
	}

	public error(): boolean {
		return (this._status === ResponseStatus.Error);
	}

}
