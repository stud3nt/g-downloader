import { BaseModel } from "./base/base-model";

export class Tag extends BaseModel {

	constructor(obj?: any) {
		super();

		Object.assign(this, obj);
	}

	private _id: number;

	private _name: string;

	get id(): number {
		return this._id;
	}

	set id(value: number) {
		this._id = value;
	}

	get name(): string {
		return this._name;
	}

	set name(value: string) {
		this._name = value;
	}
}