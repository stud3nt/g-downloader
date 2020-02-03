import { BaseModel } from "./base/base-model";
import { StatusCode } from "../enum/status-code";

export class Status extends BaseModel {

	constructor(obj?: any) {
		super();

		Object.assign(this, obj);
	}

	private _code: number = StatusCode.NoEffect;

	private _progress: number = 0;

	private _description: string = '';

	get code(): number {
		return this._code;
	}

	set code(value: number) {
		this._code = value;
	}

	get progress(): number {
		return this._progress;
	}

	set progress(value: number) {
		this._progress = value;
	}

	get description(): string {
		return this._description;
	}

	set description(value: string) {
		this._description = value;
	}
}