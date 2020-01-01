import { ToastrType } from "../enum/toastr-type";

export class CustomToastr {
	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	private _title: string = null;

	private _message: string = null;

	// toastr type (default: ERROR);
	private _type: string = ToastrType.Error;

	// is toastr shown/open? (default: true);
	private _open: boolean = true;

	// automatic closing time (ms) - default 0 (never closes automaticaly)
	private _autoClose: number = 0;

	get title(): string {
		return this._title;
	}

	set title(value: string) {
		this._title = value;
	}

	get message(): string {
		return this._message;
	}

	set message(value: string) {
		this._message = value;
	}

	get type(): string {
		return this._type;
	}

	set type(value: string) {
		this._type = value;
	}

	get open(): boolean {
		return this._open;
	}

	set open(value: boolean) {
		this._open = value;
	}

	get autoClose(): number {
		return this._autoClose;
	}

	set autoClose(value: number) {
		this._autoClose = value;
	}

}