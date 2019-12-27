import { ResponseStatus } from "../enum/response-status";

export class JsonResponse {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	public status: number = ResponseStatus.Error;

	public data: any = null;

	public success(): boolean {
		return (this.status === ResponseStatus.Success);
	}

	public error(): boolean {
		return (this.status === ResponseStatus.Error);
	}

}