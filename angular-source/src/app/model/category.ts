import { BaseModel } from "./base/base-model";

export class Category extends BaseModel {

	constructor(obj?: any) {
		super();

		Object.assign(this, obj);
	}

	private _id: number;

	private _name: string;

	private _numberOfNodes: number = 0;

	private _label: string;

	private _description: string = null;

	private _symbol: string = null;

	private _active: boolean = false;

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

	get label(): string {
		return this._label;
	}

	set label(value: string) {
		this._label = value;
	}

	get description(): string {
		return this._description;
	}

	set description(value: string) {
		this._description = value;
	}

	get symbol(): string {
		return this._symbol;
	}

	set symbol(value: string) {
		this._symbol = value;
	}

	get active(): boolean {
		return this._active;
	}

	set active(value: boolean) {
		this._active = value;
	}

	get numberOfNodes(): number {
		return this._numberOfNodes;
	}

	set numberOfNodes(value: number) {
		this._numberOfNodes = value;
	}
}